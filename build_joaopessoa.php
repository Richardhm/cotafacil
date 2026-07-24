<?php
// Gera os arquivos joaopessoa-individual.txt e joaopessoa-supersimples.txt
// a partir dos valores brutos extraídos manualmente das páginas informadas pelo usuário.

$cidadeId = 26;
$now = '2026-07-14 10:00:00';

function money(float $v): string {
    return number_format($v, 2, '.', '');
}

// ── Individual.pdf pág.11 — NOSSO MÉDICO (plano_id=23), padrão MEDICO_COLS ──
$medicoRows = [
    [364.34,369.19,546.56,551.41,261.18,266.03,391.80,396.65],
    [450.32,456.32,675.55,681.54,322.82,328.81,484.26,490.26],
    [517.87,524.77,776.88,783.77,371.24,378.13,556.90,563.80],
    [580.01,587.74,870.11,877.82,415.79,423.51,623.73,631.46],
    [609.01,617.13,913.62,921.71,436.58,444.69,654.92,663.03],
    [688.18,697.36,1032.39,1041.53,493.34,502.50,740.06,749.22],
    [855.89,867.31,1283.98,1295.35,613.57,624.96,920.41,931.80],
    [1158.62,1174.08,1738.12,1753.52,830.59,846.01,1245.96,1261.38],
    [1564.14,1585.01,2346.46,2367.25,1121.30,1142.11,1682.05,1702.86],
    [1974.10,2000.44,2961.47,2987.71,1415.19,1441.46,2122.92,2149.18],
];

// ── Individual.pdf pág.11 — PLANO INDIVIDUAL (plano_id=1), layout especial 20 colunas ──
// cols usuário 6,7,8,9 (Parcial ENF-M1,ENF-M2,APT-M1,APT-M2) e 15,16,17,18 (Com Copart ENF-M1,ENF-M2,APT-M1,APT-M2)
$planoRows = [
    [428.64,433.49,643.01,647.86,307.27,312.12,460.93,465.78],
    [529.80,535.79,794.76,800.75,379.79,385.78,569.71,575.70],
    [609.27,616.16,913.97,920.86,436.76,443.65,655.17,662.06],
    [682.38,690.10,1023.65,1031.36,489.17,496.89,733.79,741.51],
    [716.50,724.61,1074.83,1082.93,513.63,521.73,770.48,778.59],
    [809.65,818.81,1214.56,1223.71,580.40,589.55,870.64,879.81],
    [1006.96,1018.35,1510.55,1521.93,721.84,733.22,1082.81,1094.22],
    [1363.12,1378.54,2044.83,2060.24,977.15,992.56,1465.80,1481.25],
    [1840.21,1861.03,2760.52,2781.32,1319.15,1339.96,1978.83,1999.69],
    [2322.53,2348.81,3484.05,3510.30,1664.90,1691.16,2497.48,2523.81],
];

$valorPromoIndividual = 3.85;

// ── Ambulatorial.pdf pág.23 — plano_id=1, acomodacao_id=3 (AMB) ──
// [copat0-M1, copat0-M2, copat1-M1, copat1-M2] (valores crus, já sem a coluna Referência)
$ambRows = [
    [261.20,266.05,165.91,170.76],
    [323.57,329.58,205.55,211.55],
    [369.52,376.38,234.74,241.59],
    [412.75,420.42,262.20,269.86],
    [434.63,442.70,276.10,284.16],
    [488.52,497.59,310.34,319.40],
    [610.06,621.39,387.55,398.87],
    [831.82,847.27,528.42,543.86],
    [1122.96,1143.81,713.37,734.21],
    [1417.29,1443.60,900.34,926.65],
];
$valorPromoAmb = 3.85;

// ── Super_Simples.pdf pág.10 — NOSSO MÉDICO (plano_id=24), COLS_4 ──
$ssMedicoRows = [
    [264.86,396.55,198.71,297.30],
    [296.64,444.14,222.56,332.98],
    [332.24,497.44,249.27,372.94],
    [382.08,572.06,286.66,428.88],
    [439.39,657.87,329.66,493.21],
    [522.87,782.87,392.30,586.92],
    [653.59,978.59,490.38,733.65],
    [816.99,1223.24,612.98,917.06],
    [1388.88,2079.51,1042.07,1559.00],
    [1555.55,2329.05,1167.12,1746.08],
];

// ── Super_Simples.pdf pág.10 — SUPER SIMPLES (plano_id=5), COLS_6 ──
// [AMB-copat0, ENF-copat0, APT-copat0, AMB-copat1, ENF-copat1, APT-copat1]
$ssPlanoRows = [
    [211.99,311.35,466.29,135.83,233.52,349.51],
    [237.43,348.71,522.24,152.13,261.54,391.45],
    [265.92,390.56,584.91,170.39,292.92,438.42],
    [305.81,449.14,672.65,195.95,336.86,504.18],
    [351.68,516.51,773.55,225.34,387.39,579.81],
    [418.50,614.65,920.52,268.15,460.99,689.97],
    [523.13,768.31,1150.65,335.19,576.24,862.46],
    [653.91,960.39,1438.31,418.99,720.30,1078.08],
    [1111.65,1632.66,2445.13,712.28,1224.51,1832.74],
    [1245.05,1828.58,2738.55,797.75,1371.45,2052.67],
];
$valorPromoSS = 23.25;

const ACOM_APT = 1;
const ACOM_ENF = 2;
const ACOM_AMB = 3;

/**
 * @param array<int,float[]> $rows 10 linhas com 8 valores (MEDICO_COLS shape)
 * @return array acomodacaoId=>['copat'=>['odonto'=> [faixa=>valor]]]
 */
function buildMedicoColsShape(array $rows, float $valorPromo): array {
    // idx: 0 ENF-M1(cop0) 1 ENF-M2(cop0) 2 APT-M1(cop0) 3 APT-M2(cop0)
    //      4 ENF-M1(cop1) 5 ENF-M2(cop1) 6 APT-M1(cop1) 7 APT-M2(cop1)
    $defs = [
        0 => ['copat'=>0,'acom'=>ACOM_ENF,'odonto'=>1],
        1 => ['copat'=>0,'acom'=>ACOM_ENF,'odonto'=>0],
        2 => ['copat'=>0,'acom'=>ACOM_APT,'odonto'=>1],
        3 => ['copat'=>0,'acom'=>ACOM_APT,'odonto'=>0],
        4 => ['copat'=>1,'acom'=>ACOM_ENF,'odonto'=>1],
        5 => ['copat'=>1,'acom'=>ACOM_ENF,'odonto'=>0],
        6 => ['copat'=>1,'acom'=>ACOM_APT,'odonto'=>1],
        7 => ['copat'=>1,'acom'=>ACOM_APT,'odonto'=>0],
    ];
    $out = [];
    foreach ($defs as $colIdx => $def) {
        for ($f = 0; $f < 10; $f++) {
            $valor = $rows[$f][$colIdx];
            if ($def['odonto'] === 1 && $valorPromo > 0) {
                $valor = round($valor + $valorPromo, 2);
            }
            $out[$def['copat']][$def['odonto']][$def['acom']][$f + 1] = round($valor, 2);
        }
    }
    return $out;
}

/**
 * @param array<int,float[]> $rows 10 linhas com 4 valores [copat0-M1,copat0-M2,copat1-M1,copat1-M2]
 */
function buildAmbuColsShape(array $rows, float $valorPromo): array {
    $defs = [
        0 => ['copat'=>0,'odonto'=>1],
        1 => ['copat'=>0,'odonto'=>0],
        2 => ['copat'=>1,'odonto'=>1],
        3 => ['copat'=>1,'odonto'=>0],
    ];
    $out = [];
    foreach ($defs as $colIdx => $def) {
        for ($f = 0; $f < 10; $f++) {
            $valor = $rows[$f][$colIdx];
            if ($def['odonto'] === 1 && $valorPromo > 0) {
                $valor = round($valor + $valorPromo, 2);
            }
            $out[$def['copat']][$def['odonto']][ACOM_AMB][$f + 1] = round($valor, 2);
        }
    }
    return $out;
}

/** COLS_4: 0=ENF-cop0 1=APT-cop0 2=ENF-cop1 3=APT-cop1 (odonto derivado) */
function buildCols4Shape(array $rows, float $valorPromo): array {
    $defs = [
        0 => ['copat'=>0,'acom'=>ACOM_ENF],
        1 => ['copat'=>0,'acom'=>ACOM_APT],
        2 => ['copat'=>1,'acom'=>ACOM_ENF],
        3 => ['copat'=>1,'acom'=>ACOM_APT],
    ];
    $out = [];
    foreach ([1, 0] as $odonto) {
        foreach ($defs as $colIdx => $def) {
            for ($f = 0; $f < 10; $f++) {
                $valor = $rows[$f][$colIdx];
                if ($odonto === 1 && $valorPromo > 0 && $valor > 0) {
                    $valor = round($valor + $valorPromo, 2);
                }
                $out[$def['copat']][$odonto][$def['acom']][$f + 1] = round($valor, 2);
            }
        }
    }
    return $out;
}

/** COLS_6: 0=AMB-cop0 1=ENF-cop0 2=APT-cop0 3=AMB-cop1 4=ENF-cop1 5=APT-cop1 (odonto derivado) */
function buildCols6Shape(array $rows, float $valorPromo): array {
    $defs = [
        0 => ['copat'=>0,'acom'=>ACOM_AMB],
        1 => ['copat'=>0,'acom'=>ACOM_ENF],
        2 => ['copat'=>0,'acom'=>ACOM_APT],
        3 => ['copat'=>1,'acom'=>ACOM_AMB],
        4 => ['copat'=>1,'acom'=>ACOM_ENF],
        5 => ['copat'=>1,'acom'=>ACOM_APT],
    ];
    $out = [];
    foreach ([1, 0] as $odonto) {
        foreach ($defs as $colIdx => $def) {
            for ($f = 0; $f < 10; $f++) {
                $valor = $rows[$f][$colIdx];
                if ($odonto === 1 && $valorPromo > 0 && $valor > 0) {
                    $valor = round($valor + $valorPromo, 2);
                }
                $out[$def['copat']][$odonto][$def['acom']][$f + 1] = round($valor, 2);
            }
        }
    }
    return $out;
}

function zeroAcom(array &$data, int $acom): void {
    foreach ([0, 1] as $copat) {
        foreach ([1, 0] as $odonto) {
            for ($f = 1; $f <= 10; $f++) {
                $data[$copat][$odonto][$acom][$f] = 0.00;
            }
        }
    }
}

/** Emite as linhas VALUES na ordem canônica: copat ASC, odonto DESC(1,0), acom ASC(1,2,3), faixa ASC */
function emitLines(array $data, int $cidadeId, int $planoId, string $now): array {
    $lines = [];
    foreach ([0, 1] as $copat) {
        foreach ([1, 0] as $odonto) {
            foreach ([1, 2, 3] as $acom) {
                for ($f = 1; $f <= 10; $f++) {
                    $valor = $data[$copat][$odonto][$acom][$f] ?? 0.00;
                    $lines[] = sprintf(
                        "(4, %d, %d, %d, %d, %d, %d, %s, '%s', '%s')",
                        $cidadeId, $planoId, $acom, $f, $copat, $odonto, money($valor), $now, $now
                    );
                }
            }
        }
    }
    return $lines;
}

// ── Monta INDIVIDUAL.txt ──
$medicoData = buildMedicoColsShape($medicoRows, $valorPromoIndividual);
zeroAcom($medicoData, ACOM_AMB);

$planoData = buildMedicoColsShape($planoRows, $valorPromoIndividual);
$ambData = buildAmbuColsShape($ambRows, $valorPromoAmb);
foreach ($ambData as $copat => $byOdonto) {
    foreach ($byOdonto as $odonto => $byAcom) {
        $planoData[$copat][$odonto][ACOM_AMB] = $byAcom[ACOM_AMB];
    }
}

$linesMedico = emitLines($medicoData, $cidadeId, 23, $now);
$linesPlano  = emitLines($planoData, $cidadeId, 1, $now);
$allLinesIndividual = array_merge($linesMedico, $linesPlano);

$totalIndividual = count($allLinesIndividual);
$headerIndividual = <<<SQL
-- Importação Assistida: João Pessoa - PB (individual, página 11)
-- cidade_id={$cidadeId} | plano_nosso_medico=23 | plano_individual=1 | valorPromo=3.85
-- Layout especial: tabela do Plano Individual(1) tem 20 colunas (cols usuário 6,7,8,9=Parcial ENF/APT M1/M2; 15,16,17,18=Com Coparticipação ENF/APT M1/M2); tabela MIX da página foi descartada.
-- Ambulatorial (acomodacao_id=3) do plano_id=1 extraído de Ambulatorial.pdf página 23 (valorPromo=3.85, igual). plano_id=23 não vende ambulatorial (0.00).
-- total registros={$totalIndividual}

SQL;

$sqlIndividual = $headerIndividual . "\nINSERT INTO `tabelas` (`administradora_id`, `tabela_origens_id`, `plano_id`, `acomodacao_id`, `faixa_etaria_id`, `coparticipacao`, `odonto`, `valor`, `created_at`, `updated_at`) VALUES\n"
    . implode(",\n", $allLinesIndividual) . ";\n";

file_put_contents(__DIR__ . '/public/joaopessoa-individual.txt', $sqlIndividual);

// ── Monta SUPERSIMPLES.txt ──
$ssMedicoData = buildCols4Shape($ssMedicoRows, $valorPromoSS);
zeroAcom($ssMedicoData, ACOM_AMB);

$ssPlanoData = buildCols6Shape($ssPlanoRows, $valorPromoSS);

$linesSsMedico = emitLines($ssMedicoData, $cidadeId, 24, $now);
$linesSsPlano  = emitLines($ssPlanoData, $cidadeId, 5, $now);
$allLinesSS = array_merge($linesSsMedico, $linesSsPlano);

$totalSS = count($allLinesSS);
$headerSS = <<<SQL
-- Importação Assistida: João Pessoa - PB (super_simples, página 10)
-- cidade_id={$cidadeId} | plano_nosso_medico=24 | plano_super_simples=5 | valorPromo=23.25
-- plano_nosso_medico(24) segue padrão COLS_4 (sem ambulatorial, acomodacao_id=3 = 0.00). plano_super_simples(5) tem coluna S/ACOM (COLS_6), ambulatorial real.
-- total registros={$totalSS}

SQL;

$sqlSS = $headerSS . "\nINSERT INTO `tabelas` (`administradora_id`, `tabela_origens_id`, `plano_id`, `acomodacao_id`, `faixa_etaria_id`, `coparticipacao`, `odonto`, `valor`, `created_at`, `updated_at`) VALUES\n"
    . implode(",\n", $allLinesSS) . ";\n";

file_put_contents(__DIR__ . '/public/joaopessoa-supersimples.txt', $sqlSS);

echo "Individual: {$totalIndividual} registros\n";
echo "SuperSimples: {$totalSS} registros\n";
echo "Done\n";
