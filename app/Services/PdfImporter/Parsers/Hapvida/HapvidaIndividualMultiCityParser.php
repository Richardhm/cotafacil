<?php

namespace App\Services\PdfImporter\Parsers\Hapvida;

use App\Services\PdfImporter\DTOs\PageDto;
use App\Services\PdfImporter\DTOs\RowDto;
use App\Services\PdfImporter\DTOs\TableDto;
use RuntimeException;

/**
 * Parses Hapvida Individual (Nosso Plano + Nosso Médico) price tables
 * for the multi-city PDF format (one PDF covers many cities/states).
 *
 * Block detection counts monetary values per faixa row:
 *   8  values → NOSSO MÉDICO block (standard)
 *  11  values → NOSSO PLANO block (standard) — OR NOSSO MÉDICO "Integrado" (SP variant)
 *   4  values → MIX plan section  → DISCARDED
 * 2nd 8-block → PLENO plan section → DISCARDED
 *
 * Three structural variants identified in the multi-city PDF:
 *
 * 1. Standard  — [n=8 NM?] + [n=11 NP] + [n=8/4 PLENO/MIX?]
 *    Most non-SP and some SP cities (Fortaleza, Juazeiro, Araraquara, Bauru…).
 *    VALOR PROMO is col 9 of the NP block.
 *
 * 2. Limeira   — [n=10 NM] + [n=9 NP]
 *    SP cities where NM includes VALOR PROMO + VALOR as extra cols (10 total)
 *    and NP has Referência but no VALOR PROMO (9 total).
 *    VALOR PROMO is col 8 of the NM block.
 *
 * 3. Integrado — [n=11 NM] + [n=8 NP] + [n=8 PLENO?]
 *    SP cities (Ribeirão Preto, Sertãozinho) where NOSSO MÉDICO "Integrado"
 *    uses an 11-col layout; NOSSO PLANO has only 8 cols (no Referência/promo).
 *    VALOR PROMO is col 9 of the NM block.
 */
class HapvidaIndividualMultiCityParser
{
    private const COPAT_PARCIAL = 0;
    private const COPAT_TOTAL   = 1;

    private const ACOM_APARTAMENTO = 1;
    private const ACOM_ENFERMARIA  = 2;

    /**
     * 8 monetary values per NOSSO MÉDICO faixa row.
     * Order: CopParcial ENF M1/M2, APT M1/M2, CopTotal ENF M1/M2, APT M1/M2.
     */
    private const MEDICO_COLS = [
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_ENFERMARIA,  'medica' => 1], // 0
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_ENFERMARIA,  'medica' => 2], // 1
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_APARTAMENTO, 'medica' => 1], // 2
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_APARTAMENTO, 'medica' => 2], // 3
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_ENFERMARIA,  'medica' => 1], // 4
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_ENFERMARIA,  'medica' => 2], // 5
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_APARTAMENTO, 'medica' => 1], // 6
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_APARTAMENTO, 'medica' => 2], // 7
    ];

    /**
     * Layout A — ANS 436.062/01-1 and similar (most cities):
     *   CopParcial×4, Referência (col 4 — SKIP), SemCop×4, VALOR PROMO, VALOR
     *
     * Layout B — ANS 467.473/12-1 (SP interior cities: Araraquara, Bauru, Franca, ...):
     *   CopParcial×4, SemCop×4, Referência (col 8 — SKIP), VALOR PROMO, VALOR
     *
     * Detection: if firstRow[4] > 1500 → Referência is at col 4 (Layout A);
     *            otherwise Referência is at col 8 (Layout B).
     * Referência values start at ~R$3.194 for faixa 00-18; regular prices are ≤ R$700.
     */
    private const PLANO_COLS_A = [
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_ENFERMARIA,  'medica' => 1], // 0
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_ENFERMARIA,  'medica' => 2], // 1
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_APARTAMENTO, 'medica' => 1], // 2
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_APARTAMENTO, 'medica' => 2], // 3
        null,                                                                                // 4 Referência → descartada
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_ENFERMARIA,  'medica' => 1], // 5
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_ENFERMARIA,  'medica' => 2], // 6
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_APARTAMENTO, 'medica' => 1], // 7
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_APARTAMENTO, 'medica' => 2], // 8
        // idx 9  = VALOR PROMO (stop index for buildTables)
        // idx 10 = VALOR ODONTO (ignorado)
    ];

    private const PLANO_COLS_B = [
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_ENFERMARIA,  'medica' => 1], // 0
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_ENFERMARIA,  'medica' => 2], // 1
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_APARTAMENTO, 'medica' => 1], // 2
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_APARTAMENTO, 'medica' => 2], // 3
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_ENFERMARIA,  'medica' => 1], // 4
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_ENFERMARIA,  'medica' => 2], // 5
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_APARTAMENTO, 'medica' => 1], // 6
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_APARTAMENTO, 'medica' => 2], // 7
        null,                                                                                // 8 Referência → descartada
        // idx 9  = VALOR PROMO (stop index for buildTables)
        // idx 10 = VALOR ODONTO (ignorado)
    ];

    private const FAIXAS_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    private const PLAN_DB_NAMES = [
        'medico' => 'Nosso Médico', // planos.id = 23
        'plano'  => 'Individual',   // planos.id = 1
    ];

    public function canParse(string $pageText): bool
    {
        return str_contains($pageText, 'TABELA DE VENDAS PROMOCIONAL - PLANO DE SAÚDE INDIVIDUAL')
            && !str_contains($pageText, 'PORTE I');
    }

    public function parse(string $pageText, int $pageNumber): PageDto
    {
        $normText = $this->normalizeText($pageText);

        $cidade = $this->extractCidade($normText, $pageNumber);
        $uf     = $this->extractUf($normText);

        $blocks = $this->extractBlocks($normText);

        [$medicoBlock, $planoBlock, $valorPromo] = $this->classifyBlocks($blocks, $cidade, $uf, $normText);

        $tables = [];

        // ── NOSSO MÉDICO ──────────────────────────────────────────────────────
        if ($medicoBlock !== null) {
            $medicoRows = $medicoBlock['rows'];

            if (count($medicoRows) !== 10) {
                throw new RuntimeException(sprintf(
                    'NOSSO MÉDICO: esperado 10 faixas, encontrado %d (%s - %s).',
                    count($medicoRows), $cidade, $uf
                ));
            }

            $medicoRows = $this->fixM1M2Order($medicoRows, [[0, 1], [2, 3], [4, 5], [6, 7]]);
            $tables     = array_merge(
                $tables,
                $this->buildTables(self::PLAN_DB_NAMES['medico'], self::MEDICO_COLS, $medicoRows, $valorPromo, null)
            );
        }

        // ── NOSSO PLANO ───────────────────────────────────────────────────────
        $planoRows = $planoBlock['rows'];

        if (count($planoRows) !== 10) {
            throw new RuntimeException(sprintf(
                'NOSSO PLANO: esperado 10 faixas, encontrado %d (%s - %s).',
                count($planoRows), $cidade, $uf
            ));
        }

        // Layout detection: col4 > R$1500 → Referência at col 4 (Layout A), else col 8 (Layout B)
        if (($planoRows[0][4] ?? 0) > 1500.0) {
            $planoCols = self::PLANO_COLS_A;
            $planoM1M2 = [[0, 1], [2, 3], [5, 6], [7, 8]];
        } else {
            $planoCols = self::PLANO_COLS_B;
            $planoM1M2 = [[0, 1], [2, 3], [4, 5], [6, 7]];
        }

        $planoRows = $this->fixM1M2Order($planoRows, $planoM1M2);
        $tables    = array_merge(
            $tables,
            $this->buildTables(self::PLAN_DB_NAMES['plano'], $planoCols, $planoRows, $valorPromo, 9)
        );

        return new PageDto(
            cidade: $cidade,
            uf: $uf,
            administradora: 'Hapvida',
            valorPromoOdonto: $valorPromo,
            tables: $tables,
            pageNumber: $pageNumber,
        );
    }

    // ─── Block classification ──────────────────────────────────────────────

    /**
     * Classifies extracted blocks into (medicoBlock|null, planoBlock, valorPromo).
     *
     * @param  array{count:int,rows:float[][]}[] $blocks
     * @return array{0:array|null, 1:array, 2:float}  [medicoBlock|null, planoBlock, valorPromo]
     */
    private function classifyBlocks(array $blocks, string $cidade, string $uf, string $normText): array
    {
        if (empty($blocks)) {
            throw new RuntimeException("NOSSO PLANO não encontrado ({$cidade} - {$uf}).");
        }

        $firstN = $blocks[0]['count'];

        // ── Variant 2 "Limeira": NM(10) → NP(9) ──────────────────────────
        // NM has 8 price cols + VALOR PROMO (col 8) + VALOR (col 9).
        // NP has 8 price cols + Referência (col 8, skip) — no VALOR PROMO col.
        // VALOR PROMO is extracted from NM col 8.
        if ($firstN === 10) {
            $medicoBlock = $blocks[0];
            $valorPromo  = $medicoBlock['rows'][0][8] ?? 0.0;

            foreach (array_slice($blocks, 1) as $b) {
                if ($b['count'] === 9) {
                    return [$medicoBlock, $b, $valorPromo];
                }
            }

            throw new RuntimeException("NOSSO PLANO (9 valores/faixa) não encontrado ({$cidade} - {$uf}).");
        }

        // ── Variant 3 "Integrado": NM(11) → NP(8) → [PLENO(8)] ──────────
        // Ribeirão Preto and Sertãozinho: NOSSO MÉDICO "Integrado" uses the
        // same 11-col structure as NOSSO PLANO Layout B.  Identified by the
        // "INTEGRADO" label that appears at the top of those PDF pages.
        // VALOR PROMO is col 9 of the NM block.
        if ($firstN === 11 && str_contains($normText, 'INTEGRADO')) {
            foreach (array_slice($blocks, 1) as $b) {
                if ($b['count'] === 8) {
                    $medicoBlock = $blocks[0];
                    $valorPromo  = $medicoBlock['rows'][0][9] ?? 0.0;
                    return [$medicoBlock, $b, $valorPromo];
                }
            }
        }

        // ── Variant 1 "Standard": [NM(8)?] + NP(11) + [PLENO(8)/MIX(4)] ──
        // Covers all remaining cases:
        //  - NP-only pages (Fortaleza, Camaçari, …): firstN=11, no n=8 blocks
        //  - NM+NP pages (Juazeiro, …): firstN=8, NP found in second block
        //  - Pages where first block n=11 but no INTEGRADO (Joinville): NP first, NM second
        $medicoBlock = null;
        $planoBlock  = null;
        $valorPromo  = 0.0;

        foreach ($blocks as $b) {
            if ($b['count'] === 8 && $medicoBlock === null) {
                $medicoBlock = $b;
            } elseif ($b['count'] === 11 && $planoBlock === null) {
                $planoBlock = $b;
                $valorPromo = $b['rows'][0][9] ?? 0.0;
            }
            // n=4 (MIX), n=9/10 (handled by Limeira variant above), second n=8 (PLENO) → discarded
        }

        if ($planoBlock === null) {
            throw new RuntimeException("NOSSO PLANO (11 valores/faixa) não encontrado ({$cidade} - {$uf}).");
        }

        return [$medicoBlock, $planoBlock, $valorPromo];
    }

    // ─── Block extraction ──────────────────────────────────────────────────

    /**
     * Groups consecutive faixa rows by monetary-value count into blocks.
     *
     * Value counts are preserved exactly for 8, 9, 10; capped at 11 for larger.
     * Blocks are capped at 10 rows to prevent PLENO or second tables (both n=8)
     * from merging into the NOSSO PLANO block when no n-change occurs between them.
     *
     * @return array<int, array{count: int, rows: array<int, float[]>}>
     */
    private function extractBlocks(string $text): array
    {
        preg_match_all(
            '/(?:\d{1,2}\s*[Aa]\s*\d{1,2}\s*[Aa][Nn][Oo][Ss]|59\s*[Aa][Nn][Oo][Ss]\s*[Oo][Uu]\s*[Mm][Aa][Ii][Ss])([^\n]*)/u',
            $text,
            $matches,
            PREG_SET_ORDER
        );

        $blocks  = [];
        $current = null;

        foreach ($matches as $match) {
            $values = $this->extractMonetaryValues($match[0]);
            $raw    = count($values);

            if ($raw < 4) {
                continue;
            }

            // Preserve 8/9/10 as distinct; anything ≥11 is treated as 11
            $n = match (true) {
                $raw >= 11  => 11,
                $raw === 10 => 10,
                $raw === 9  => 9,
                $raw >= 8   => 8,
                default     => 4,
            };

            // Start a new block on count change OR when the current block is full (10 rows).
            // The 10-row cap prevents NP+PLENO from merging when both emit n=8 rows.
            if ($current === null || $current['count'] !== $n || count($current['rows']) >= 10) {
                if ($current !== null) {
                    $blocks[] = $current;
                }
                $current = ['count' => $n, 'rows' => []];
            }

            $current['rows'][] = array_slice($values, 0, $n);
        }

        if ($current !== null) {
            $blocks[] = $current;
        }

        return $blocks;
    }

    // ─── Text normalization ────────────────────────────────────────────────

    /**
     * Collapses letter-spaced decorative headings like "N O S S O   M É D I C O" → "NOSSO MÉDICO".
     */
    private function normalizeText(string $text): string
    {
        return preg_replace_callback(
            '/\b([A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇÀ](?:\s[A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇÀ]){2,})\b/u',
            fn($m) => preg_replace('/\s+/', '', $m[1]),
            $text
        );
    }

    // ─── Header extraction ─────────────────────────────────────────────────

    private function extractCidade(string $text, int $pageNumber): string
    {
        // Primary: city name on the line right after the table header
        if (preg_match(
            '/TABELA DE VENDAS[^\n]*INDIVIDUAL\s*\n\s*([A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ ]*?)\s*-\s*[A-Z]{2}\b/u',
            $text, $m
        )) {
            return trim($m[1]);
        }

        // Fallback: "CIDADE - UF, ___ de" from the signature block.
        // Character class intentionally uses only space (not \s) to prevent
        // matching across newlines and capturing multi-line garbage.
        if (preg_match(
            '/([A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ ]{3,}?)\s*-\s*(?:GO|MG|SP|RJ|BA|CE|PE|PR|RS|SC|DF|ES|PA|AM|MT|MS|RO|TO|PI|MA|AL|SE|RN|PB|AC|AP|RR)\s*,\s*_{3}/u',
            $text, $m
        )) {
            return trim($m[1]);
        }

        throw new RuntimeException("Não foi possível identificar a cidade (página {$pageNumber}).");
    }

    private function extractUf(string $text): string
    {
        if (preg_match(
            '/TABELA DE VENDAS[^\n]*INDIVIDUAL\s*\n\s*[^\n]+-\s*([A-Z]{2})\b/u',
            $text, $m
        )) {
            return $m[1];
        }

        if (preg_match(
            '/[A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ ]{4,}\s*-\s*(GO|MG|SP|RJ|BA|CE|PE|PR|RS|SC|DF|ES|PA|AM|MT|MS|RO|TO|PI|MA|AL|SE|RN|PB|AC|AP|RR)\b/u',
            $text, $m
        )) {
            return $m[1];
        }

        return '';
    }

    // ─── Value extraction ──────────────────────────────────────────────────

    /** @return float[] */
    private function extractMonetaryValues(string $text): array
    {
        preg_match_all('/\b(\d{1,3}(?:\.\d{3})*,\d{2})\b(?!\s*%)/u', $text, $m);

        return array_map(
            fn($v) => (float) str_replace(['.', ','], ['', '.'], $v),
            $m[1]
        );
    }

    // ─── Table building ────────────────────────────────────────────────────

    /**
     * @param array<int, array{copat:int,acom:int,medica:int}|null> $colDefs
     * @param array<int, float[]> $faixaRows
     * @return TableDto[]
     */
    private function buildTables(
        string $planName,
        array  $colDefs,
        array  $faixaRows,
        float  $valorPromo,
        ?int   $valorPromoIndex
    ): array {
        $byCopatOdonto = [];

        foreach ($colDefs as $colIndex => $def) {
            if ($valorPromoIndex !== null && $colIndex >= $valorPromoIndex) {
                break;
            }

            if ($def === null) {
                continue;
            }

            $copat  = $def['copat'];
            $acom   = $def['acom'];
            $isM1   = ($def['medica'] === 1);
            $odonto = $isM1 ? 1 : 0;

            foreach ($faixaRows as $faixaIndex => $values) {
                $faixaId = self::FAIXAS_IDS[$faixaIndex];
                $valor   = $values[$colIndex] ?? 0.0;

                if ($isM1 && $valorPromo > 0) {
                    $valor = round($valor + $valorPromo, 2);
                }

                $byCopatOdonto[$copat][$odonto][$acom][$faixaId] = $valor;
            }
        }

        $tables = [];
        foreach ($byCopatOdonto as $copat => $byOdonto) {
            foreach ($byOdonto as $odonto => $byAcom) {
                $rows = [];
                foreach (self::FAIXAS_IDS as $faixaId) {
                    $apt    = $byAcom[self::ACOM_APARTAMENTO][$faixaId] ?? 0.0;
                    $enf    = $byAcom[self::ACOM_ENFERMARIA][$faixaId]  ?? 0.0;
                    $rows[] = new RowDto($faixaId, $apt, $enf);
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

    // ─── Sanity check ──────────────────────────────────────────────────────

    /**
     * Ensures M1 <= M2 for each pair (Médica¹ has discount so it should be cheaper).
     * Swaps across all faixas when the first row is out of order.
     *
     * @param array<int, float[]> $rows
     * @param int[][] $pairs Each entry is [m1ColIndex, m2ColIndex]
     */
    private function fixM1M2Order(array $rows, array $pairs): array
    {
        foreach ($pairs as [$m1Idx, $m2Idx]) {
            $m1 = $rows[0][$m1Idx] ?? 0;
            $m2 = $rows[0][$m2Idx] ?? 0;

            if ($m1 > 0 && $m2 > 0 && $m1 > $m2) {
                foreach ($rows as &$row) {
                    [$row[$m1Idx], $row[$m2Idx]] = [$row[$m2Idx], $row[$m1Idx]];
                }
                unset($row);
            }
        }

        return $rows;
    }
}
