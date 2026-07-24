<?php

namespace App\Services\ImportacaoAssistida\Readers;

use App\Services\ImportacaoAssistida\DTOs\RegistroDto;
use RuntimeException;

/**
 * Parses one page of Ambulatorial.pdf.
 *
 * Each page has 10 faixa rows × 7 monetary values each:
 *
 *   idx 0: COM COPART  – AMB – M1  (odonto=1: valor + valorPromo)
 *   idx 1: COM COPART  – AMB – M2  (odonto=0: valor as-is)
 *   idx 2: Referência                (skip)
 *   idx 3: SEM COPART  – AMB – M1  (odonto=1: valor + valorPromo)  [stored as copat=1]
 *   idx 4: SEM COPART  – AMB – M2  (odonto=0)
 *   idx 5: VALOR PROMO               (extracted, added to M1 values)
 *   idx 6: VALOR ODONTO              (ignored)
 *
 * Fixed mappings:
 *   plano_id      = 1  (Individual — Nosso Plano Ambulatorial)
 *   acomodacao_id = 3  (Ambulatorial)
 *
 * DB stores "Sem Coparticipação" as copat=1 (legacy mapping, same as IndividualReader).
 */
class AmbulatorialReader
{
    const PLANO_INDIVIDUAL  = 1;
    const ACOM_AMBULATORIAL = 3;

    private const FAIXAS_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    private const AMBU_COLS = [
        0 => ['copat' => 0, 'odonto' => 1],  // CopParcial M1
        1 => ['copat' => 0, 'odonto' => 0],  // CopParcial M2
        // 2 = Referência → skip
        3 => ['copat' => 1, 'odonto' => 1],  // SemCop M1
        4 => ['copat' => 1, 'odonto' => 0],  // SemCop M2
        // 5 = VALOR PROMO → extracted below
        // 6 = VALOR ODONTO → ignored
    ];

    /**
     * @return RegistroDto[]
     * @throws RuntimeException
     */
    public function parse(string $pageText): array
    {
        $rows = $this->extractFaixaRows($pageText);

        if (count($rows) !== 10) {
            throw new RuntimeException(
                sprintf('Ambulatorial: esperado 10 faixas, encontrado %d.', count($rows))
            );
        }

        $valorPromo = $rows[0][5] ?? 0.0;

        $rows = $this->fixM1M2Order($rows, [[0, 1], [3, 4]]);

        return $this->buildRegistros($rows, $valorPromo);
    }

    /**
     * Returns the raw text and extracted faixa rows for layout analysis.
     */
    public function analyzeRaw(string $pageText): array
    {
        preg_match_all(
            '/(?:\d{1,2}\s*[Aa]\s*\d{1,2}\s*[Aa][Nn][Oo][Ss]|59\s*[Aa][Nn][Oo][Ss]\s*[Oo][Uu]\s*[Mm][Aa][Ii][Ss])([^\n]*)/u',
            $pageText,
            $matches,
            PREG_SET_ORDER,
        );

        $faixaRows = [];
        foreach ($matches as $match) {
            preg_match_all('/\b(\d{1,3}(?:\.\d{3})*,\d{2})\b(?!\s*%)/u', $match[0], $m);
            $faixaRows[] = [
                'linha'   => trim($match[0]),
                'valores' => $m[1],
                'count'   => count($m[1]),
            ];
        }

        return [
            'texto_bruto'  => $pageText,
            'faixa_rows'   => $faixaRows,
            'total_faixas' => count($faixaRows),
        ];
    }

    // ─── Internals ─────────────────────────────────────────────────────────

    /** @return RegistroDto[] */
    private function buildRegistros(array $faixaRows, float $valorPromo): array
    {
        $registros = [];

        foreach (self::AMBU_COLS as $colIdx => $def) {
            foreach (self::FAIXAS_IDS as $faixaIdx => $faixaId) {
                $valor = $faixaRows[$faixaIdx][$colIdx] ?? 0.0;

                if ($def['odonto'] === 1 && $valorPromo > 0) {
                    $valor = round($valor + $valorPromo, 2);
                }

                $registros[] = new RegistroDto(
                    planoId:        self::PLANO_INDIVIDUAL,
                    acomodacaoId:   self::ACOM_AMBULATORIAL,
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
     * Extracts all faixa rows, returning rows with exactly 7 monetary values.
     * Rows with other counts are ignored (headers, percentages, etc.).
     *
     * @return array<int, float[]>
     */
    private function extractFaixaRows(string $text): array
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
            if (count($values) === 7) {
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
     * Ensures M1 (cheaper, discounted) <= M2 for each pair.
     *
     * @param array<int, float[]> $rows
     * @param int[][] $pairs
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
