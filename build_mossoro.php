<?php
// Gera os arquivos mossoro-individual.txt e mossoro-supersimples.txt
// mesmo padrão de layout de João Pessoa (cidade_id=26).

$cidadeId = 33;
$now = '2026-07-14 11:00:00';

function money(float $v): string {
    return number_format($v, 2, '.', '');
}

// ── Individual.pdf pág.21 — NOSSO MÉDICO (plano_id=23), padrão MEDICO_COLS ──
$medicoRows = [
    [235.54,251.44,353.32,369.22,163.96,179.86,245.94,261.84],
    [291.13,310.78,436.70,456.36,202.65,222.31,303.98,323.63],
    [334.80,357.40,502.21,524.81,233.05,255.66,349.58,372.17],
    [374.98,400.29,562.48,587.79,261.02,286.34,391.53,416.83],
    [393.73,420.30,590.60,617.18,274.07,300.66,411.11,437.67],
    [444.91,474.94,667.38,697.41,309.70,339.75,464.55,494.57],
    [553.33,590.68,830.02,867.37,385.17,422.55,577.76,615.10],
    [749.04,799.60,1123.60,1174.16,521.40,572.01,782.11,832.66],
    [1011.20,1079.46,1516.86,1585.12,703.89,772.21,1055.85,1124.09],
    [1276.24,1362.39,1914.43,2000.58,888.38,974.61,1332.59,1418.71],
];

// ── Individual.pdf pág.21 — PLANO INDIVIDUAL (plano_id=1), layout especial 20 colunas ──
// mesmas posições de João Pessoa: cols usuário 6,7,8,9 (Parcial ENF/APT M1/M2) e 15,16,17,18 (Com Copart ENF/APT M1/M2)
$planoRows = [
    [277.11,293.01,415.67,431.57,192.89,208.79,289.33,305.23],
    [342.51,362.16,513.77,533.42,238.39,258.04,357.61,377.26],
    [393.89,416.48,590.84,613.43,274.15,296.75,411.25,433.85],
    [441.16,466.46,661.74,687.04,307.05,332.36,460.60,485.91],
    [463.22,489.78,694.83,721.39,322.40,348.98,483.63,510.21],
    [523.44,553.45,785.16,815.17,364.31,394.35,546.50,576.54],
    [651.00,688.33,976.50,1013.83,453.09,490.45,679.68,717.04],
    [881.26,931.79,1321.89,1372.42,613.35,663.92,920.08,970.66],
    [1189.70,1257.92,1784.55,1852.77,828.02,896.29,1242.11,1310.39],
    [1501.52,1587.62,2252.28,2338.38,1045.04,1131.21,1567.67,1653.84],
];

$valorPromoIndividual = 14.90;

// ── Ambulatorial.pdf pág.15 — plano_id=1, acomodacao_id=3 (AMB) ──
// [copat0-M1, copat0-M2, copat1-M1, copat1-M2]
$ambRows = [
    [166.27,171.12,122.96,127.81],
    [205.98,211.98,152.32,158.33],
    [235.23,242.08,173.95,180.81],
    [262.75,270.40,194.30,201.96],
    [276.68,284.73,204.60,212.66],
    [310.99,320.04,229.97,239.03],
    [388.36,399.67,287.19,298.50],
    [529.53,544.95,391.58,407.00],
    [714.87,735.68,528.63,549.45],
    [902.24,928.50,667.18,693.46],
];
$valorPromoAmb = 3.85; // valor da própria página do Ambulatorial.pdf (fixo, igual em outras cidades)

// ── Super_Simples.pdf pág.7 — NOSSO MÉDICO (plano_id=24), COLS_4 ──
$ssMedicoRows = [
    [146.50,219.14,116.41,174.02],
    [164.08,245.44,130.38,194.90],
    [183.77,274.89,146.03,218.29],
    [211.34,316.12,167.93,251.03],
    [243.04,363.54,193.12,288.68],
    [289.22,432.61,229.81,343.53],
    [361.53,540.76,287.26,429.41],
    [451.91,675.95,359.08,536.76],
    [768.25,1149.12,610.44,912.49],
    [860.44,1287.01,683.69,1021.99],
];

// ── Super_Simples.pdf pág.7 — SUPER SIMPLES (plano_id=5), COLS_6 ──
// [AMB-copat0, ENF-copat0, APT-copat0, AMB-copat1, ENF-copat1, APT-copat1]
$ssPlanoRows = [
    [127.70,172.14,257.58,101.52,136.74,204.52],
    [143.02,192.80,288.49,113.70,153.15,229.06],
    [160.18,215.94,323.11,127.34,171.53,256.55],
    [184.21,248.33,371.58,146.44,197.26,295.03],
    [211.84,285.58,427.32,168.41,226.85,339.28],
    [252.09,339.84,508.51,200.41,269.95,403.74],
    [315.11,424.80,635.64,250.51,337.44,504.68],
    [393.89,531.00,794.55,313.14,421.80,630.85],
    [669.61,902.70,1350.74,532.34,717.06,1072.45],
    [749.96,1011.02,1512.83,596.22,803.11,1201.14],
];
$valorPromoSS = 23.25; // "PREMIUM NACIONAL" — fixo nacionalmente

const ACOM_APT = 1;
const ACOM_ENF = 2;
const ACOM_AMB = 3;

function buildMedicoColsShape(array $rows, float $valorPromo): array {
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
-- Importação Assistida: Mossoró - RN (individual, página 21)
-- cidade_id={$cidadeId} | plano_nosso_medico=23 | plano_individual=1 | valorPromo=14.90
-- Layout especial (mesmo padrão de João Pessoa): tabela do Plano Individual(1) tem 20 colunas na mesma linha (cols usuário 6,7,8,9=Parcial ENF/APT M1/M2; 15,16,17,18=Com Coparticipação ENF/APT M1/M2); tabela MIX/REFERÊNCIA descartada.
-- Ambulatorial (acomodacao_id=3) do plano_id=1 extraído de Ambulatorial.pdf página 15 (valorPromo=3.85 — valor fixo da própria página do Ambulatorial.pdf, DIFERENTE do valorPromo=14.90 do Individual.pdf desta cidade). plano_id=23 não vende ambulatorial (0.00).
-- total registros={$totalIndividual}

SQL;

$sqlIndividual = $headerIndividual . "\nINSERT INTO `tabelas` (`administradora_id`, `tabela_origens_id`, `plano_id`, `acomodacao_id`, `faixa_etaria_id`, `coparticipacao`, `odonto`, `valor`, `created_at`, `updated_at`) VALUES\n"
    . implode(",\n", $allLinesIndividual) . ";\n";

file_put_contents(__DIR__ . '/public/mossoro-individual.txt', $sqlIndividual);

// ── Monta SUPERSIMPLES.txt ──
$ssMedicoData = buildCols4Shape($ssMedicoRows, $valorPromoSS);
zeroAcom($ssMedicoData, ACOM_AMB);

$ssPlanoData = buildCols6Shape($ssPlanoRows, $valorPromoSS);

$linesSsMedico = emitLines($ssMedicoData, $cidadeId, 24, $now);
$linesSsPlano  = emitLines($ssPlanoData, $cidadeId, 5, $now);
$allLinesSS = array_merge($linesSsMedico, $linesSsPlano);

$totalSS = count($allLinesSS);
$headerSS = <<<SQL
-- Importação Assistida: Mossoró - RN (super_simples, página 7)
-- cidade_id={$cidadeId} | plano_nosso_medico=24 | plano_super_simples=5 | valorPromo=23.25
-- plano_nosso_medico(24) segue padrão COLS_4 (sem ambulatorial, acomodacao_id=3 = 0.00). plano_super_simples(5) tem coluna S/ACOM (COLS_6), ambulatorial real.
-- total registros={$totalSS}

SQL;

$sqlSS = $headerSS . "\nINSERT INTO `tabelas` (`administradora_id`, `tabela_origens_id`, `plano_id`, `acomodacao_id`, `faixa_etaria_id`, `coparticipacao`, `odonto`, `valor`, `created_at`, `updated_at`) VALUES\n"
    . implode(",\n", $allLinesSS) . ";\n";

file_put_contents(__DIR__ . '/public/mossoro-supersimples.txt', $sqlSS);

echo "Individual: {$totalIndividual} registros\n";
echo "SuperSimples: {$totalSS} registros\n";
echo "Done\n";
