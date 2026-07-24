<?php

namespace App\Services\PdfImporter\Parsers\Hapvida;

use App\Services\PdfImporter\DTOs\PageDto;
use App\Services\PdfImporter\DTOs\RowDto;
use App\Services\PdfImporter\DTOs\TableDto;
use RuntimeException;

/**
 * Parser for Hapvida Super Simples PDFs (2 a 29 vidas).
 *
 * Three page layouts:
 *
 *   A) NOSSO MÉDICO + NOSSO PLANO  → 20 faixas (str_contains 'NOSSO M')
 *      SP-interior variant           → 30 faixas: NOSSO MÉDICO + NOSSO PLANO + PLENO
 *      Each faixa has 4 values (no S/ ACOM) or 6 values (with S/ ACOM column).
 *
 *   B) NOSSO PLANO only / NOSSO PLANO 2  → 10 faixas (no 'NOSSO M', but 'NOSSO PLANO')
 *      Both "NOSSO PLANO" and "NOSSO PLANO 2" map to Super Simples (plano_id = 5).
 *      Each faixa has 4 or 6 values (same S/ ACOM detection as layout A).
 *
 *   C) INTEGRADO + PLENO  → INTEGRADO 10 faixas × 2 values + PLENO 10 faixas × 4/6 values.
 *
 * Column order per faixa row (4-value — no S/ ACOM):
 *   idx 0: ENF  – COM COPARTICIPAÇÃO PARCIAL (copat=0)
 *   idx 1: APT  – COM COPARTICIPAÇÃO PARCIAL (copat=0)
 *   idx 2: ENF  – COM COPARTICIPAÇÃO [TOTAL]  (copat=1)
 *   idx 3: APT  – COM COPARTICIPAÇÃO [TOTAL]  (copat=1)
 *
 * Column order per faixa row (6-value — with S/ ACOM column):
 *   idx 0: ENF    – copat=0
 *   idx 1: APT    – copat=0
 *   idx 2: S/ACOM – copat=0  → acomodacao_id = 3 (Ambulatorial)
 *   idx 3: ENF    – copat=1
 *   idx 4: APT    – copat=1
 *   idx 5: S/ACOM – copat=1  → acomodacao_id = 3 (Ambulatorial)
 *
 * Column order per faixa row (2-value — INTEGRADO only, ENF-only plan):
 *   idx 0: ENF  – copat=0
 *   idx 1: ENF  – copat=1
 *
 * Odonto:
 *   odonto=0 → price as-is from PDF (PDF always shows Sem Odonto values)
 *   odonto=1 → price + VALOR PROMO (PREMIUM NACIONAL, e.g. R$23,25)
 *              Only added when base price > 0 (INTEGRADO APT stays R$0,00)
 */
class HapvidaSuperSimplesParser
{
    private const COPAT_PARCIAL = 0;
    private const COPAT_TOTAL   = 1;

    private const ACOM_ENFERMARIA   = 2;
    private const ACOM_APARTAMENTO  = 1;
    private const ACOM_AMBULATORIAL = 3;

    private const FAIXAS_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    /** 4-value rows: ENF/APT × copat (no S/ ACOM) */
    private const COLS_4 = [
        0 => ['acom' => self::ACOM_ENFERMARIA,  'copat' => self::COPAT_PARCIAL],
        1 => ['acom' => self::ACOM_APARTAMENTO, 'copat' => self::COPAT_PARCIAL],
        2 => ['acom' => self::ACOM_ENFERMARIA,  'copat' => self::COPAT_TOTAL],
        3 => ['acom' => self::ACOM_APARTAMENTO, 'copat' => self::COPAT_TOTAL],
    ];

    /** 6-value rows: ENF/APT/S·ACOM × copat */
    private const COLS_6 = [
        0 => ['acom' => self::ACOM_ENFERMARIA,   'copat' => self::COPAT_PARCIAL],
        1 => ['acom' => self::ACOM_APARTAMENTO,  'copat' => self::COPAT_PARCIAL],
        2 => ['acom' => self::ACOM_AMBULATORIAL, 'copat' => self::COPAT_PARCIAL],
        3 => ['acom' => self::ACOM_ENFERMARIA,   'copat' => self::COPAT_TOTAL],
        4 => ['acom' => self::ACOM_APARTAMENTO,  'copat' => self::COPAT_TOTAL],
        5 => ['acom' => self::ACOM_AMBULATORIAL, 'copat' => self::COPAT_TOTAL],
    ];

    /** 2-value rows: ENF only (INTEGRADO — APT always R$0,00) */
    private const COLS_2 = [
        0 => ['acom' => self::ACOM_ENFERMARIA, 'copat' => self::COPAT_PARCIAL],
        1 => ['acom' => self::ACOM_ENFERMARIA, 'copat' => self::COPAT_TOTAL],
    ];

    private const PLAN_DB_NAMES = [
        'nosso_medico' => 'Super Simples - Nosso Médico', // plano_id = 24
        'nosso_plano'  => 'Super Simples',                // plano_id = 5
        'integrado'    => 'Super Simples - Integrado',    // plano_id = 10
        'pleno'        => 'Super Simples - Pleno',        // plano_id = 11
    ];

    public function canParse(string $pageText): bool
    {
        return str_contains($pageText, 'PORTE I (de 2 a 15 vidas)');
    }

    public function parse(string $pageText, int $pageNumber): PageDto
    {
        $normText   = $this->normalizeText($pageText);
        $cidade     = $this->extractCidade($normText);
        $uf         = $this->extractUf($normText);
        $valorPromo = $this->extractValorPromo($normText);

        $allRows = $this->extractAllFaixaRows($pageText);

        if (str_contains($pageText, 'NOSSO M')) {
            // Layout A: NOSSO MÉDICO (10) + NOSSO PLANO[2] (10)
            $tables = $this->parseNossoMedicoPlano($allRows, $valorPromo, $pageNumber);
        } elseif (str_contains($pageText, 'NOSSO PLANO')) {
            // Layout B: NOSSO PLANO or NOSSO PLANO 2 only (10 faixas)
            // "NOSSO PLANO 2" is a variant registration; both → Super Simples plano_id=5
            $tables = $this->parseNossoPlanoOnly($allRows, $valorPromo, $pageNumber);
        } else {
            // Layout C: INTEGRADO + PLENO
            $tables = $this->parseIntegradoPleno($allRows, $valorPromo, $pageNumber);
        }

        return new PageDto(
            cidade: $cidade,
            uf: $uf,
            administradora: 'Hapvida',
            valorPromoOdonto: $valorPromo,
            tables: $tables,
            pageNumber: $pageNumber,
        );
    }

    // ─── Section parsers ──────────────────────────────────────────────────

    /** @return TableDto[] */
    private function parseNossoMedicoPlano(array $allRows, float $valorPromo, int $pageNumber): array
    {
        $count = count($allRows);

        if ($count === 20) {
            // Standard: NOSSO MÉDICO (10) + NOSSO PLANO (10)
            $medicoRows = array_values(array_slice($allRows, 0, 10));
            $planoRows  = array_values(array_slice($allRows, 10, 10));

            return array_merge(
                $this->buildTables(self::PLAN_DB_NAMES['nosso_medico'], $this->selectCols($medicoRows), $medicoRows, $valorPromo),
                $this->buildTables(self::PLAN_DB_NAMES['nosso_plano'],  $this->selectCols($planoRows),  $planoRows,  $valorPromo),
            );
        }

        if ($count === 30) {
            // SP-interior: NOSSO MÉDICO (10) + NOSSO PLANO (10) + PLENO (10)
            $medicoRows = array_values(array_slice($allRows, 0, 10));
            $planoRows  = array_values(array_slice($allRows, 10, 10));
            $plenoRows  = array_values(array_slice($allRows, 20, 10));

            return array_merge(
                $this->buildTables(self::PLAN_DB_NAMES['nosso_medico'], $this->selectCols($medicoRows), $medicoRows, $valorPromo),
                $this->buildTables(self::PLAN_DB_NAMES['nosso_plano'],  $this->selectCols($planoRows),  $planoRows,  $valorPromo),
                $this->buildTables(self::PLAN_DB_NAMES['pleno'],        $this->selectCols($plenoRows),  $plenoRows,  $valorPromo),
            );
        }

        if ($count === 10) {
            // Merged-row layout: both NM and NP on same faixa line.
            $valCount = count($allRows[0] ?? []);

            if ($valCount === 8) {
                // Salvador-style: NM copat0(ENF,APT) | NP copat0(SACOM,ENF,APT) | NP copat1(SACOM,ENF,APT)
                $nmCols = [
                    0 => ['acom' => self::ACOM_ENFERMARIA,  'copat' => self::COPAT_PARCIAL],
                    1 => ['acom' => self::ACOM_APARTAMENTO, 'copat' => self::COPAT_PARCIAL],
                ];
                $npCols = [
                    2 => ['acom' => self::ACOM_AMBULATORIAL, 'copat' => self::COPAT_PARCIAL],
                    3 => ['acom' => self::ACOM_ENFERMARIA,   'copat' => self::COPAT_PARCIAL],
                    4 => ['acom' => self::ACOM_APARTAMENTO,  'copat' => self::COPAT_PARCIAL],
                    5 => ['acom' => self::ACOM_AMBULATORIAL, 'copat' => self::COPAT_TOTAL],
                    6 => ['acom' => self::ACOM_ENFERMARIA,   'copat' => self::COPAT_TOTAL],
                    7 => ['acom' => self::ACOM_APARTAMENTO,  'copat' => self::COPAT_TOTAL],
                ];
                return array_merge(
                    $this->buildTables(self::PLAN_DB_NAMES['nosso_medico'], $nmCols, $allRows, $valorPromo),
                    $this->buildTables(self::PLAN_DB_NAMES['nosso_plano'],  $npCols, $allRows, $valorPromo),
                );
            }

            if ($valCount === 10) {
                // MG-cities style: NP copat0(SACOM,ENF,APT) | NP copat1(SACOM,ENF,APT) | NM copat0(ENF,APT) | NM copat1(ENF,APT)
                $npCols = [
                    0 => ['acom' => self::ACOM_AMBULATORIAL, 'copat' => self::COPAT_PARCIAL],
                    1 => ['acom' => self::ACOM_ENFERMARIA,   'copat' => self::COPAT_PARCIAL],
                    2 => ['acom' => self::ACOM_APARTAMENTO,  'copat' => self::COPAT_PARCIAL],
                    3 => ['acom' => self::ACOM_AMBULATORIAL, 'copat' => self::COPAT_TOTAL],
                    4 => ['acom' => self::ACOM_ENFERMARIA,   'copat' => self::COPAT_TOTAL],
                    5 => ['acom' => self::ACOM_APARTAMENTO,  'copat' => self::COPAT_TOTAL],
                ];
                $nmCols = [
                    6 => ['acom' => self::ACOM_ENFERMARIA,  'copat' => self::COPAT_PARCIAL],
                    7 => ['acom' => self::ACOM_APARTAMENTO, 'copat' => self::COPAT_PARCIAL],
                    8 => ['acom' => self::ACOM_ENFERMARIA,  'copat' => self::COPAT_TOTAL],
                    9 => ['acom' => self::ACOM_APARTAMENTO, 'copat' => self::COPAT_TOTAL],
                ];
                return array_merge(
                    $this->buildTables(self::PLAN_DB_NAMES['nosso_plano'],  $npCols, $allRows, $valorPromo),
                    $this->buildTables(self::PLAN_DB_NAMES['nosso_medico'], $nmCols, $allRows, $valorPromo),
                );
            }

            throw new RuntimeException(
                sprintf('Página %d: 10 faixas × %d valores — layout mesclado não suportado.', $pageNumber, $valCount)
            );
        }

        throw new RuntimeException(
            sprintf('Página %d (NOSSO MÉDICO/PLANO): esperado 20, 30 ou 10 faixas mescladas, encontrado %d.', $pageNumber, $count)
        );
    }

    /** @return TableDto[] */
    private function parseNossoPlanoOnly(array $allRows, float $valorPromo, int $pageNumber): array
    {
        if (count($allRows) !== 10) {
            throw new RuntimeException(
                sprintf('Página %d (NOSSO PLANO): esperado 10 faixas, encontrado %d.', $pageNumber, count($allRows))
            );
        }

        $cols = $this->selectCols($allRows);

        return $this->buildTables(self::PLAN_DB_NAMES['nosso_plano'], $cols, $allRows, $valorPromo);
    }

    /** @return TableDto[] */
    private function parseIntegradoPleno(array $allRows, float $valorPromo, int $pageNumber): array
    {
        // INTEGRADO rows have exactly 2 values (ENF only); PLENO rows have ≥4 values.
        $integradoRows = array_values(array_filter($allRows, fn($r) => count($r) === 2));
        $plenoRows     = array_values(array_filter($allRows, fn($r) => count($r) >= 4));

        if (count($integradoRows) !== 10) {
            throw new RuntimeException(
                sprintf('Página %d (INTEGRADO): esperado 10 faixas, encontrado %d.', $pageNumber, count($integradoRows))
            );
        }
        if (count($plenoRows) !== 10) {
            throw new RuntimeException(
                sprintf('Página %d (PLENO): esperado 10 faixas, encontrado %d.', $pageNumber, count($plenoRows))
            );
        }

        $plenoCols = $this->selectCols($plenoRows);

        return array_merge(
            $this->buildTables(self::PLAN_DB_NAMES['integrado'], self::COLS_2, $integradoRows, $valorPromo),
            $this->buildTables(self::PLAN_DB_NAMES['pleno'],     $plenoCols,   $plenoRows,     $valorPromo),
        );
    }

    /**
     * Selects the correct column definition based on value count of the first row.
     * 6+ values → COLS_6 (has S/ ACOM / Ambulatorial column).
     * 4+ values → COLS_4 (standard ENF + APT).
     *
     * @param float[][] $rows
     * @return array<int, array{acom:int, copat:int}>
     */
    private function selectCols(array $rows): array
    {
        $firstCount = count($rows[0] ?? []);
        return $firstCount >= 6 ? self::COLS_6 : self::COLS_4;
    }

    // ─── Table building ────────────────────────────────────────────────────

    /**
     * Builds TableDtos (copat × odonto combinations) from raw faixa rows.
     * odonto=1 adds valorPromo only to non-zero prices.
     *
     * @param array<int, array{acom:int,copat:int}> $colDefs
     * @param array<int, float[]> $faixaRows
     * @return TableDto[]
     */
    private function buildTables(string $planName, array $colDefs, array $faixaRows, float $valorPromo): array
    {
        $byCopatAcom = [];
        foreach ($colDefs as $idx => $def) {
            foreach ($faixaRows as $faixaIdx => $values) {
                $faixaId = self::FAIXAS_IDS[$faixaIdx];
                $byCopatAcom[$def['copat']][$def['acom']][$faixaId] = $values[$idx] ?? 0.0;
            }
        }

        $tables = [];
        foreach ([self::COPAT_PARCIAL, self::COPAT_TOTAL] as $copat) {
            if (!isset($byCopatAcom[$copat])) {
                continue;
            }

            foreach ([0, 1] as $odonto) {
                $rows = [];

                foreach (self::FAIXAS_IDS as $faixaId) {
                    $enf = $byCopatAcom[$copat][self::ACOM_ENFERMARIA][$faixaId]    ?? 0.0;
                    $apt = $byCopatAcom[$copat][self::ACOM_APARTAMENTO][$faixaId]   ?? 0.0;
                    $amb = $byCopatAcom[$copat][self::ACOM_AMBULATORIAL][$faixaId]  ?? 0.0;

                    if ($odonto === 1 && $valorPromo > 0) {
                        if ($enf > 0) $enf = round($enf + $valorPromo, 2);
                        if ($apt > 0) $apt = round($apt + $valorPromo, 2);
                        if ($amb > 0) $amb = round($amb + $valorPromo, 2);
                    }

                    $rows[] = new RowDto($faixaId, $apt, $enf, $amb);
                }

                $tables[] = new TableDto(
                    plano: $planName,
                    coparticipacao: $copat,
                    odonto: (bool) $odonto,
                    rows: $rows,
                );
            }
        }

        return $tables;
    }

    // ─── Faixa row extraction ──────────────────────────────────────────────

    /**
     * Extracts all faixa rows from the page.
     * Each entry is an array of monetary float values found on that faixa line.
     *
     * @return array<int, float[]>
     */
    private function extractAllFaixaRows(string $text): array
    {
        preg_match_all(
            '/(?:\d{1,2}\s*[Aa]\s*\d{1,2}\s*[Aa][Nn][Oo][Ss]|59\s*[Aa][Nn][Oo][Ss]\s*[Oo][Uu]\s*[Mm][Aa][Ii][Ss])([^\n]*)/u',
            $text,
            $matches,
            PREG_SET_ORDER
        );

        $rows = [];
        foreach ($matches as $match) {
            $values = $this->extractMonetaryValues($match[0]);
            if (!empty($values)) {
                $rows[] = $values;
            }
        }

        return $rows;
    }

    /** @return float[] */
    private function extractMonetaryValues(string $text): array
    {
        preg_match_all('/\b(\d{1,3}(?:\.\d{3})*,\d{2})\b(?!\s*%)/u', $text, $m);

        return array_map(
            fn($v) => (float) str_replace(['.', ','], ['', '.'], $v),
            $m[1]
        );
    }

    // ─── Header extraction ─────────────────────────────────────────────────

    private function extractCidade(string $text): string
    {
        if (preg_match('/TABELA DE VENDAS[^\n]*\n\s*([A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ\s]*?)\s*-\s*[A-Z]{2}\b/u', $text, $m)) {
            return trim($m[1]);
        }

        if (preg_match('/([A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ\s]{3,}?)\s*-\s*(GO|MG|SP|RJ|BA|CE|PE|PR|RS|SC|DF|ES|PA|AM|MT|MS|RO|TO|PI|MA|AL|SE|RN|PB|AC|AP|RR)\b/u', $text, $m)) {
            return trim($m[1]);
        }

        throw new RuntimeException('Não foi possível identificar a cidade no PDF Super Simples.');
    }

    private function extractUf(string $text): string
    {
        if (preg_match('/TABELA DE VENDAS[^\n]*\n\s*[^\n]+-\s*([A-Z]{2})\b/u', $text, $m)) {
            return $m[1];
        }

        if (preg_match('/[A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ\s]{4,}\s*-\s*(GO|MG|SP|RJ|BA|CE|PE|PR|RS|SC|DF|ES|PA|AM|MT|MS|RO|TO|PI|MA|AL|SE|RN|PB|AC|AP|RR)\b/u', $text, $m)) {
            return $m[1];
        }

        return '';
    }

    private function extractValorPromo(string $text): float
    {
        // "PREMIUM NACIONAL  ...  R$ 78,87  R$ 23,25" — last monetary value = VALOR PROMO
        if (preg_match('/PREMIUM\s*NACIONAL[^\n]+/u', $text, $lineMatch)) {
            $values = $this->extractMonetaryValues($lineMatch[0]);
            if (!empty($values)) {
                return end($values);
            }
        }

        return 0.0;
    }

    // ─── Text normalization ────────────────────────────────────────────────

    private function normalizeText(string $text): string
    {
        return preg_replace_callback(
            '/\b([A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇÀ](?:\s[A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇÀ]){2,})\b/u',
            fn($m) => preg_replace('/\s+/', '', $m[1]),
            $text
        );
    }
}
