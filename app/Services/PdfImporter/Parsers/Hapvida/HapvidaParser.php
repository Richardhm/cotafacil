<?php

namespace App\Services\PdfImporter\Parsers\Hapvida;

use App\Services\PdfImporter\DTOs\PageDto;
use App\Services\PdfImporter\DTOs\RowDto;
use App\Services\PdfImporter\DTOs\TableDto;
use RuntimeException;

class HapvidaParser
{
    private const COPAT_PARCIAL    = 0;
    private const COPAT_TOTAL      = 1;
    private const COPAT_SEM        = 2;
    private const COPAT_REFERENCIA = 3;

    private const ACOM_APARTAMENTO = 1;
    private const ACOM_ENFERMARIA  = 2;

    /**
     * 8 monetary values per NOSSO MÉDICO faixa row.
     * Order: CopParcial ENF M1/M2, APT M1/M2, CopTotal ENF M1/M2, APT M1/M2.
     */
    private const MEDICO_COLS = [
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_ENFERMARIA,  'medica' => 1],
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_ENFERMARIA,  'medica' => 2],
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_APARTAMENTO, 'medica' => 1],
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_APARTAMENTO, 'medica' => 2],
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_ENFERMARIA,  'medica' => 1],
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_ENFERMARIA,  'medica' => 2],
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_APARTAMENTO, 'medica' => 1],
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_APARTAMENTO, 'medica' => 2],
    ];

    /**
     * 11 monetary values per NOSSO PLANO faixa row.
     * Physical column order in PDF: CopParcial ENF M1/M2, APT M1/M2, Referência (idx 4 — SKIPPED),
     * SemCop ENF M1/M2, APT M1/M2, VALOR PROMO (idx 9), VALOR ODONTO (idx 10).
     * null entries mark columns to skip without importing.
     */
    private const PLANO_COLS = [
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_ENFERMARIA,  'medica' => 1], // idx 0: CopParcial ENF M1
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_ENFERMARIA,  'medica' => 2], // idx 1: CopParcial ENF M2
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_APARTAMENTO, 'medica' => 1], // idx 2: CopParcial APT M1
        ['copat' => self::COPAT_PARCIAL, 'acom' => self::ACOM_APARTAMENTO, 'medica' => 2], // idx 3: CopParcial APT M2
        null,                                                                                // idx 4: Referência → descartada
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_ENFERMARIA,  'medica' => 1], // idx 5: CopTotal ENF M1
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_ENFERMARIA,  'medica' => 2], // idx 6: CopTotal ENF M2
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_APARTAMENTO, 'medica' => 1], // idx 7: CopTotal APT M1
        ['copat' => self::COPAT_TOTAL,   'acom' => self::ACOM_APARTAMENTO, 'medica' => 2], // idx 8: CopTotal APT M2
        // idx 9 = VALOR PROMO (valorPromoIndex)
        // idx 10 = VALOR ODONTO (ignorado)
    ];

    private const FAIXAS_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    public function canParse(string $pageText): bool
    {
        return str_contains($pageText, 'M I X');
    }

    public function parse(string $pageText, int $pageNumber): PageDto
    {
        // Split on "M I X" in raw text BEFORE normalization, because
        // normalizeText() collapses "M I X" → "MIX" making the marker invisible.
        $mixPos = strpos($pageText, 'M I X');
        if ($mixPos === false) {
            throw new RuntimeException("Marcador 'M I X' não encontrado na página {$pageNumber}.");
        }

        $rawMedico = substr($pageText, 0, $mixPos);
        $rawPlano  = substr($pageText, $mixPos);

        $medicoText = $this->normalizeText($rawMedico);
        $planoText  = $this->normalizeText($rawPlano);
        $normFull   = $this->normalizeText($pageText);

        // valorPromo is constant across all faixa rows (e.g. R$ 24,50); read from first row.
        $valorPromo = $this->extractValorPromo($planoText);

        $cidade = $this->extractCidade($normFull);
        $uf     = $this->extractUf($normFull);

        $tables = array_merge(
            $this->parseMedicoSection($medicoText, $valorPromo),
            $this->parsePlanoSection($planoText, $valorPromo),
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

    // ─── Text normalization ────────────────────────────────────────────────

    /**
     * Collapses letter-spaced decorative headings like "N O S S O   M É D I C O" → "NOSSO MÉDICO".
     * Matches sequences of 3+ single uppercase letters (including accented) separated by single spaces.
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

    private function extractCidade(string $text): string
    {
        // Primary: "TABELA DE VENDAS...INDIVIDUAL\nCIDADE - UF"
        if (preg_match('/TABELA DE VENDAS[^\n]*INDIVIDUAL\s*\n\s*([A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ\s]*?)\s*-\s*[A-Z]{2}\b/u', $text, $m)) {
            return trim($m[1]);
        }

        // Fallback: any "CIDADE - GO/MG/SP/..." pattern anywhere in text
        if (preg_match('/([A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ\s]{3,}?)\s*-\s*(GO|MG|SP|RJ|BA|CE|PE|PR|RS|SC|DF|ES|PA|AM|MT|MS|RO|TO|PI|MA|AL|SE|RN|PB|AC|AP|RR)\b/u', $text, $m)) {
            return trim($m[1]);
        }

        throw new RuntimeException('Não foi possível identificar a cidade no PDF.');
    }

    private function extractUf(string $text): string
    {
        if (preg_match('/TABELA DE VENDAS[^\n]*INDIVIDUAL\s*\n\s*[^\n]+-\s*([A-Z]{2})\b/u', $text, $m)) {
            return $m[1];
        }

        if (preg_match('/[A-ZÁÉÍÓÚÂÊÎÔÛÃÕÇ\s]{4,}\s*-\s*(GO|MG|SP|RJ|BA|CE|PE|PR|RS|SC|DF|ES|PA|AM|MT|MS|RO|TO|PI|MA|AL|SE|RN|PB|AC|AP|RR)\b/u', $text, $m)) {
            return $m[1];
        }

        return '';
    }

    // ─── DB plan name mapping ──────────────────────────────────────────────

    // Maps PDF section names to the corresponding planos.nome in the database.
    private const PLAN_DB_NAMES = [
        'medico' => 'Nosso Médico', // planos.id = 23
        'plano'  => 'Individual',   // planos.id = 1
    ];

    // ─── NOSSO MÉDICO parsing ──────────────────────────────────────────────

    /** @return TableDto[] */
    private function parseMedicoSection(string $text, float $valorPromo): array
    {
        $faixaRows = $this->extractFaixaRows($text, 8);

        if (count($faixaRows) !== 10) {
            throw new RuntimeException(
                sprintf('NOSSO MÉDICO: esperado 10 faixas, encontrado %d.', count($faixaRows))
            );
        }

        $faixaRows = $this->fixM1M2Order($faixaRows, [[0, 1], [2, 3], [4, 5], [6, 7]]);

        return $this->buildTables(self::PLAN_DB_NAMES['medico'], self::MEDICO_COLS, $faixaRows, $valorPromo, null);
    }

    // ─── NOSSO PLANO parsing ───────────────────────────────────────────────

    /** @return TableDto[] */
    private function parsePlanoSection(string $text, float $valorPromo): array
    {
        $faixaRows = $this->extractFaixaRows($text, 11);

        if (count($faixaRows) !== 10) {
            throw new RuntimeException(
                sprintf('NOSSO PLANO: esperado 10 faixas, encontrado %d.', count($faixaRows))
            );
        }

        $faixaRows = $this->fixM1M2Order($faixaRows, [[0, 1], [2, 3], [5, 6], [7, 8]]);

        return $this->buildTables(self::PLAN_DB_NAMES['plano'], self::PLANO_COLS, $faixaRows, $valorPromo, 9);
    }

    // ─── Value extraction ──────────────────────────────────────────────────

    /**
     * Extracts rows matching faixa etária patterns.
     * Returns array of float arrays (one per faixa), each sliced to $expectedValueCount.
     *
     * @return array<int, float[]>
     */
    private function extractFaixaRows(string $text, int $expectedValueCount): array
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

            if (count($values) < $expectedValueCount) {
                continue;
            }

            $rows[] = array_slice($values, 0, $expectedValueCount);
        }

        return $rows;
    }

    /**
     * Extracts all Brazilian monetary values (handles "R$ 1.234,56" and "1.234,56 R$").
     * Excludes percentages like "40,00%".
     *
     * @return float[]
     */
    private function extractMonetaryValues(string $text): array
    {
        preg_match_all('/\b(\d{1,3}(?:\.\d{3})*,\d{2})\b(?!\s*%)/u', $text, $m);

        return array_map(
            fn($v) => (float) str_replace(['.', ','], ['', '.'], $v),
            $m[1]
        );
    }

    private function extractValorPromo(string $text): float
    {
        // valorPromo is always at index 9 in every NOSSO PLANO row; same value across all faixas.
        $rows = $this->extractFaixaRows($text, 10);
        if (!empty($rows)) {
            return $rows[0][9] ?? 0.0;
        }

        return 0.0;
    }

    // ─── Table building ────────────────────────────────────────────────────

    /**
     * Converts raw faixa rows + column definitions into TableDtos.
     *
     * @param array<array{copat:int,acom:int,medica:int,is_ref?:bool}> $colDefs
     * @param array<int, float[]> $faixaRows 10 faixas, each with N values.
     * @param int|null $valorPromoIndex Column index where VALOR PROMO starts (null = no valorPromo in this section).
     * @return TableDto[]
     */
    private function buildTables(
        string $planName,
        array  $colDefs,
        array  $faixaRows,
        float  $valorPromo,
        ?int   $valorPromoIndex
    ): array {
        // Build per-(copat, odonto, acom) value maps
        $byCopatOdonto = [];

        foreach ($colDefs as $colIndex => $def) {
            if ($valorPromoIndex !== null && $colIndex >= $valorPromoIndex) {
                break;
            }

            if ($def === null) {
                continue; // coluna descartada (ex: Referência)
            }

            $copat  = $def['copat'];
            $acom   = $def['acom'];
            $isM1   = ($def['medica'] === 1);
            $odonto = $isM1 ? 1 : 0; // Médica¹ = Com Odonto, Médica² = Sem Odonto

            foreach ($faixaRows as $faixaIndex => $values) {
                $faixaId = self::FAIXAS_IDS[$faixaIndex];
                $valor   = $values[$colIndex] ?? 0.0;

                // Médica¹ column = médica price WITH odonto bundle (discounted médica).
                // "Com Odonto" total = M1 médica (discounted) + VALOR PROMO (odonto monthly price).
                // This is the total monthly amount the customer pays when buying both products together.
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
                    $apt  = $byAcom[self::ACOM_APARTAMENTO][$faixaId] ?? 0.0;
                    $enf  = $byAcom[self::ACOM_ENFERMARIA][$faixaId]  ?? 0.0;
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

    // ─── Sanity checks ─────────────────────────────────────────────────────

    /**
     * Ensures M1 <= M2 for each pair (Médica¹ has discount so it's always cheaper).
     * Swaps the pair across all faixas if the order is wrong.
     * Handles PDF text extraction quirks where column order may be reversed on some pages.
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
