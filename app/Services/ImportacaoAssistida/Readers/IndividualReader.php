<?php

namespace App\Services\ImportacaoAssistida\Readers;

use App\Services\ImportacaoAssistida\DTOs\RegistroDto;
use RuntimeException;

/**
 * Parses one page of Individual.pdf.
 *
 * Two page layouts exist depending on whether the city sells PLENO:
 *
 * ── Layout "sem Pleno" (e.g. Belém) — 2 tables, 20 faixa rows total ────────
 *   8 values/row  → NOSSO MÉDICO  (plano_id = 23)
 *  11 values/row  → NOSSO PLANO   (plano_id = 1) — includes Referência skip
 *                   (idx 4) + VALOR PROMO (idx 9) + VALOR ODONTO (idx 10)
 *
 * ── Layout "com Pleno" (e.g. Sertãozinho) — 3 tables, 30 faixa rows total ──
 *   Appear in this order: NOSSO MÉDICO, NOSSO PLANO, PLENO.
 *   NOSSO MÉDICO carries the extra trailing columns instead of NOSSO PLANO:
 *     idx 0-7: the 8 relevant values (same shape as MEDICO_COLS below)
 *     idx 8:   REFERÊNCIA / INTEGRADO total (discarded — different product,
 *              not one of our 3 import targets)
 *     idx 9:   VALOR PROMO (extracted, added to all M1 values)
 *     idx10:   VALOR ODONTO (ignored)
 *   NOSSO PLANO and PLENO are then plain 8-value tables (no skip, no extras).
 *
 * Which layout a page uses is detected by how many groups of 10 faixa rows
 * are found (2 vs 3) — value-count alone can't disambiguate NOSSO PLANO from
 * PLENO since both are plain 8-value tables in the "com Pleno" layout.
 *
 * ── Column layout shared by NOSSO MÉDICO / NOSSO PLANO / PLENO (8 values) ──
 *   idx 0: CopParcial – ENF – M1  (odonto=1: valor + valorPromo)
 *   idx 1: CopParcial – ENF – M2  (odonto=0: valor as-is)
 *   idx 2: CopParcial – APT – M1  (odonto=1)
 *   idx 3: CopParcial – APT – M2  (odonto=0)
 *   idx 4: CopTotal   – ENF – M1  (odonto=1)
 *   idx 5: CopTotal   – ENF – M2  (odonto=0)
 *   idx 6: CopTotal   – APT – M1  (odonto=1)
 *   idx 7: CopTotal   – APT – M2  (odonto=0)
 *
 * ── NOSSO PLANO column layout when it carries the extras (11 values) ──────
 *   idx 0: CopParcial – ENF – M1  (odonto=1)
 *   idx 1: CopParcial – ENF – M2  (odonto=0)
 *   idx 2: CopParcial – APT – M1  (odonto=1)
 *   idx 3: CopParcial – APT – M2  (odonto=0)
 *   idx 4: Referência              (skip)
 *   idx 5: SemCop     – ENF – M1  (odonto=1)  [stored as copat=1 in DB]
 *   idx 6: SemCop     – ENF – M2  (odonto=0)
 *   idx 7: SemCop     – APT – M1  (odonto=1)
 *   idx 8: SemCop     – APT – M2  (odonto=0)
 *   idx 9: VALOR PROMO             (extracted, added to all M1 values)
 *   idx10: VALOR ODONTO            (ignored)
 *
 * M1 = com odonto (discounted bundle price); M2 = sem odonto (full price).
 * DB stores "Sem Coparticipação" for NOSSO PLANO as copat=1 (legacy mapping).
 */
class IndividualReader
{
    const PLANO_INDIVIDUAL   = 1;
    const PLANO_NOSSO_MEDICO = 23;
    const PLANO_PLENO        = 38;

    private const ACOM_ENFERMARIA  = 2;
    private const ACOM_APARTAMENTO = 1;

    private const FAIXAS_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    private const MEDICO_COLS = [
        0 => ['copat' => 0, 'acom' => self::ACOM_ENFERMARIA,  'odonto' => 1],
        1 => ['copat' => 0, 'acom' => self::ACOM_ENFERMARIA,  'odonto' => 0],
        2 => ['copat' => 0, 'acom' => self::ACOM_APARTAMENTO, 'odonto' => 1],
        3 => ['copat' => 0, 'acom' => self::ACOM_APARTAMENTO, 'odonto' => 0],
        4 => ['copat' => 1, 'acom' => self::ACOM_ENFERMARIA,  'odonto' => 1],
        5 => ['copat' => 1, 'acom' => self::ACOM_ENFERMARIA,  'odonto' => 0],
        6 => ['copat' => 1, 'acom' => self::ACOM_APARTAMENTO, 'odonto' => 1],
        7 => ['copat' => 1, 'acom' => self::ACOM_APARTAMENTO, 'odonto' => 0],
    ];

    private const PLANO_COLS = [
        0 => ['copat' => 0, 'acom' => self::ACOM_ENFERMARIA,  'odonto' => 1],
        1 => ['copat' => 0, 'acom' => self::ACOM_ENFERMARIA,  'odonto' => 0],
        2 => ['copat' => 0, 'acom' => self::ACOM_APARTAMENTO, 'odonto' => 1],
        3 => ['copat' => 0, 'acom' => self::ACOM_APARTAMENTO, 'odonto' => 0],
        // 4 = Referência → skip
        5 => ['copat' => 1, 'acom' => self::ACOM_ENFERMARIA,  'odonto' => 1],
        6 => ['copat' => 1, 'acom' => self::ACOM_ENFERMARIA,  'odonto' => 0],
        7 => ['copat' => 1, 'acom' => self::ACOM_APARTAMENTO, 'odonto' => 1],
        8 => ['copat' => 1, 'acom' => self::ACOM_APARTAMENTO, 'odonto' => 0],
    ];

    /**
     * @return RegistroDto[]
     * @throws RuntimeException
     */
    public function parse(string $pageText): array
    {
        $allRows = $this->extractAllFaixaRows($pageText);
        $chunks  = array_chunk($allRows, 10);

        return match (count($chunks)) {
            2       => $this->parseSemPleno($chunks[0], $chunks[1]),
            3       => $this->parseComPleno($chunks[0], $chunks[1], $chunks[2]),
            default => throw new RuntimeException(
                sprintf('Esperado 20 ou 30 faixas (2 ou 3 tabelas), encontrado %d.', count($allRows))
            ),
        };
    }

    /** @return RegistroDto[] */
    private function parseSemPleno(array $groupA, array $groupB): array
    {
        $medicoRows = count($groupA[0] ?? []) === 8 ? $groupA : $groupB;
        $planoRows  = count($groupA[0] ?? []) >= 11 ? $groupA : $groupB;

        if (count($medicoRows) !== 10) {
            throw new RuntimeException(
                sprintf('NOSSO MÉDICO: esperado 10 faixas, encontrado %d.', count($medicoRows))
            );
        }

        if (count($planoRows) !== 10) {
            throw new RuntimeException(
                sprintf('NOSSO PLANO: esperado 10 faixas, encontrado %d.', count($planoRows))
            );
        }

        // Truncate plano rows to exactly 11 values (discard any extra)
        $planoRows = array_map(fn($r) => array_slice($r, 0, 11), $planoRows);

        $valorPromo = $planoRows[0][9] ?? 0.0;

        $medicoRows = $this->fixM1M2Order($medicoRows, [[0, 1], [2, 3], [4, 5], [6, 7]]);
        $planoRows  = $this->fixM1M2Order($planoRows,  [[0, 1], [2, 3], [5, 6], [7, 8]]);

        return array_merge(
            $this->buildRegistros(self::PLANO_NOSSO_MEDICO, self::MEDICO_COLS, $medicoRows, $valorPromo),
            $this->buildRegistros(self::PLANO_INDIVIDUAL,   self::PLANO_COLS,  $planoRows,  $valorPromo),
        );
    }

    /** @return RegistroDto[] */
    private function parseComPleno(array $medicoRows, array $planoRows, array $plenoRows): array
    {
        foreach (['NOSSO MÉDICO' => $medicoRows, 'NOSSO PLANO' => $planoRows, 'PLENO' => $plenoRows] as $label => $rows) {
            if (count($rows) !== 10) {
                throw new RuntimeException(
                    sprintf('%s: esperado 10 faixas, encontrado %d.', $label, count($rows))
                );
            }
        }

        // NOSSO MÉDICO carries the extra columns here: idx0-7 relevant,
        // idx8 REFERÊNCIA (discard), idx9 VALOR PROMO, idx10 VALOR ODONTO (discard)
        $medicoRows = array_map(fn($r) => array_slice($r, 0, 11), $medicoRows);
        $valorPromo = $medicoRows[0][9] ?? 0.0;

        $medicoRows = $this->fixM1M2Order($medicoRows, [[0, 1], [2, 3], [4, 5], [6, 7]]);
        $planoRows  = $this->fixM1M2Order($planoRows,  [[0, 1], [2, 3], [4, 5], [6, 7]]);
        $plenoRows  = $this->fixM1M2Order($plenoRows,  [[0, 1], [2, 3], [4, 5], [6, 7]]);

        return array_merge(
            $this->buildRegistros(self::PLANO_NOSSO_MEDICO, self::MEDICO_COLS, $medicoRows, $valorPromo),
            $this->buildRegistros(self::PLANO_INDIVIDUAL,   self::MEDICO_COLS, $planoRows,  $valorPromo),
            $this->buildRegistros(self::PLANO_PLENO,        self::MEDICO_COLS, $plenoRows,  $valorPromo),
        );
    }

    // ─── Internals ─────────────────────────────────────────────────────────

    /** @return RegistroDto[] */
    private function buildRegistros(int $planoId, array $colDefs, array $faixaRows, float $valorPromo): array
    {
        $registros = [];

        foreach ($colDefs as $colIdx => $def) {
            foreach (self::FAIXAS_IDS as $faixaIdx => $faixaId) {
                $valor = $faixaRows[$faixaIdx][$colIdx] ?? 0.0;

                if ($def['odonto'] === 1 && $valorPromo > 0) {
                    $valor = round($valor + $valorPromo, 2);
                }

                $registros[] = new RegistroDto(
                    planoId:        $planoId,
                    acomodacaoId:   $def['acom'],
                    faixaEtariaId:  $faixaId,
                    coparticipacao: $def['copat'],
                    odonto:         $def['odonto'],
                    valorPdf:       round($valor, 2),
                );
            }
        }

        return $registros;
    }

    /**
     * Extracts all faixa rows from the page, returning each row with ALL its monetary values.
     * No minimum threshold — classification is done by the caller based on count.
     *
     * @return array<int, float[]>
     */
    private function extractAllFaixaRows(string $text): array
    {
        preg_match_all(
            '/(?:\d{1,2}\s*[Aa]\s*\d{1,2}\s*[Aa][Nn][Oo][Ss]|59\s*[Aa][Nn][Oo][Ss]\s*[Oo][Uu]\s*[Mm][Aa][Ii][Ss])([^\n]*)/u',
            $text,
            $matches,
            PREG_SET_ORDER,
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
            $m[1],
        );
    }

    /**
     * Ensures M1 (discounted, cheaper) <= M2 for each pair.
     * Swaps across all faixa rows when PDF extraction reverses the order.
     *
     * @param array<int, float[]> $rows
     * @param int[][] $pairs  Each entry is [m1ColIndex, m2ColIndex]
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
