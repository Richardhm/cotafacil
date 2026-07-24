<?php
// Gera os arquivos recife-individual.txt e recife-supersimples.txt
// cidade_id=25, Recife - PE.
// Individual.pdf pág.23: 3 tabelas — Nosso Médico(23, 8v limpo), Nosso Plano(1, 11v com cauda
// Referência+valorPromo+valor descartada), Mix(4v, descartado inteiramente, não rastreado).
// Super_Simples.pdf pág.12: layout padrão — Nosso Médico(24, COLS_4) + Super Simples(5, COLS_6).
// Ambulatorial.pdf pág.19: plano_id=1/acomodacao_id=3 (AMB), padrão AMBU_COLS.

$cidadeId = 25;
$now = '2026-07-16 12:00:00';

function money(float $v): string {
    return number_format($v, 2, '.', '');
}

// ── Individual.pdf pág.23 — NOSSO MÉDICO (plano_id=23), padrão MEDICO_COLS ──
$medicoRows = [
    [273.55,299.05,410.34,435.84,192.50,218.00,288.73,314.23],
    [338.11,369.63,507.18,538.70,237.93,269.45,356.87,388.39],
    [388.83,425.07,583.26,619.51,273.62,309.87,410.40,446.65],
    [435.49,476.08,653.25,693.85,306.45,347.05,459.65,500.25],
    [457.26,499.88,685.91,728.54,321.77,364.40,482.63,525.26],
    [516.70,564.86,775.08,823.25,363.60,411.77,545.37,593.54],
    [642.62,702.52,963.97,1023.88,452.21,512.12,678.28,738.19],
    [869.91,951.00,1304.93,1386.03,612.16,693.26,918.19,999.29],
    [1174.38,1283.85,1761.66,1871.14,826.42,935.90,1239.56,1349.04],
    [1482.18,1620.35,2223.39,2361.57,1043.02,1181.20,1564.45,1702.62],
];

// ── Individual.pdf pág.23 — NOSSO PLANO (plano_id=1), 11 valores/linha ──
// idx0-7 MEDICO_COLS; idx8=Referência(descartar); idx9=valorPromo; idx10=valor odonto(descartar)
$planoRows = [
    [321.82,347.32,482.76,508.26,226.47,251.97,339.68,365.18],
    [397.77,429.29,596.69,628.21,279.92,311.43,419.84,451.36],
    [457.44,493.68,686.19,722.44,321.91,358.14,482.82,519.06],
    [512.33,552.92,768.53,809.13,360.54,401.12,540.76,581.35],
    [537.95,580.57,806.96,849.59,378.57,421.18,567.80,610.42],
    [607.88,656.04,911.86,960.04,427.78,475.93,641.61,689.77],
    [756.02,815.92,1134.08,1194.00,532.03,591.91,797.97,857.87],
    [1023.42,1104.51,1535.20,1616.32,720.21,801.27,1080.21,1161.30],
    [1381.62,1491.09,2072.52,2182.03,972.28,1081.71,1458.28,1567.76],
    [1743.74,1881.90,2615.73,2753.94,1227.11,1365.23,1840.50,1978.67],
];
$valorPromoIndividual = 24.50; // extraído da própria tabela Nosso Plano (idx9), constante nas 10 linhas

// ── Ambulatorial.pdf pág.19 — plano_id=1, acomodacao_id=3 (AMB) ──
// [copat0-M1, copat0-M2, copat1-M1, copat1-M2] (coluna Referência ~10x maior já descartada)
$ambRows = [
    [200.68,205.53,127.47,132.32],
    [248.60,254.61,157.91,163.92],
    [283.90,290.76,180.33,187.20],
    [317.12,324.78,201.43,209.10],
    [333.93,341.99,212.11,220.18],
    [375.34,384.40,238.41,247.48],
    [468.72,480.04,297.73,309.05],
    [639.10,654.53,405.95,421.39],
    [862.79,883.62,548.03,568.88],
    [1088.93,1115.22,691.67,717.98],
];
$valorPromoAmb = 3.85; // valor da própria página do Ambulatorial.pdf

// ── Super_Simples.pdf pág.12 — NOSSO MÉDICO (plano_id=24), COLS_4 ──
$ssMedicoRows = [
    [211.06,315.85,167.84,251.06],
    [236.39,353.75,187.98,281.19],
    [264.76,396.20,210.54,314.93],
    [304.47,455.63,242.12,362.17],
    [350.14,523.97,278.44,416.50],
    [416.67,623.52,331.34,495.64],
    [520.84,779.40,414.18,619.55],
    [651.05,974.25,517.73,774.44],
    [1106.79,1656.23,880.14,1316.55],
    [1239.60,1854.98,985.76,1474.54],
];

// ── Super_Simples.pdf pág.12 — SUPER SIMPLES (plano_id=5), COLS_6 ──
// [AMB-copat0, ENF-copat0, APT-copat0, AMB-copat1, ENF-copat1, APT-copat1]
$ssPlanoRows = [
    [217.03,248.06,371.35,139.00,197.20,295.11],
    [243.07,277.83,415.91,155.68,220.86,330.52],
    [272.24,311.17,465.82,174.36,247.36,370.18],
    [313.08,357.85,535.69,200.51,284.46,425.71],
    [360.04,411.53,616.04,230.59,327.13,489.57],
    [428.45,489.72,733.09,274.40,389.28,582.59],
    [535.56,612.15,916.36,343.00,486.60,728.24],
    [669.45,765.19,1145.45,428.75,608.25,910.30],
    [1138.07,1300.82,1947.27,728.88,1034.03,1547.51],
    [1274.64,1456.92,2180.94,816.35,1158.11,1733.21],
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
-- Importação Assistida: Recife - PE (individual, página 23)
-- cidade_id={$cidadeId} | plano_nosso_medico=23 | plano_individual=1 | valorPromo=24.50
-- Página tem 3 tabelas: Nosso Médico(23, 8 valores limpo), Nosso Plano(1, 11 valores: idx0-7 MEDICO_COLS + idx8 Referência descartado + idx9 valorPromo + idx10 valor odonto descartado), e Mix(4 valores, produto à parte não rastreado no banco — descartado inteiramente, mesmo padrão de Salvador).
-- Ambulatorial (acomodacao_id=3) do plano_id=1 extraído de Ambulatorial.pdf página 19 (valorPromo=3.85, fixo nacional).
-- total registros={$totalIndividual}

SQL;

$sqlIndividual = $headerIndividual . "\nINSERT INTO `tabelas` (`administradora_id`, `tabela_origens_id`, `plano_id`, `acomodacao_id`, `faixa_etaria_id`, `coparticipacao`, `odonto`, `valor`, `created_at`, `updated_at`) VALUES\n"
    . implode(",\n", $allLinesIndividual) . ";\n";

file_put_contents(__DIR__ . '/public/recife-individual.txt', $sqlIndividual);

// ── Monta SUPERSIMPLES.txt ──
$ssMedicoData = buildCols4Shape($ssMedicoRows, $valorPromoSS);
zeroAcom($ssMedicoData, ACOM_AMB);

$ssPlanoData = buildCols6Shape($ssPlanoRows, $valorPromoSS);

$linesSsMedico = emitLines($ssMedicoData, $cidadeId, 24, $now);
$linesSsPlano  = emitLines($ssPlanoData, $cidadeId, 5, $now);
$allLinesSS = array_merge($linesSsMedico, $linesSsPlano);

$totalSS = count($allLinesSS);
$headerSS = <<<SQL
-- Importação Assistida: Recife - PE (super_simples, página 12)
-- cidade_id={$cidadeId} | plano_nosso_medico=24 | plano_super_simples=5 | valorPromo=23.25
-- Layout padrão: plano_nosso_medico(24) COLS_4 (sem ambulatorial, acomodacao_id=3 = 0.00); plano_super_simples(5) COLS_6 (com coluna S/ACOM, ambulatorial real).
-- total registros={$totalSS}

SQL;

$sqlSS = $headerSS . "\nINSERT INTO `tabelas` (`administradora_id`, `tabela_origens_id`, `plano_id`, `acomodacao_id`, `faixa_etaria_id`, `coparticipacao`, `odonto`, `valor`, `created_at`, `updated_at`) VALUES\n"
    . implode(",\n", $allLinesSS) . ";\n";

file_put_contents(__DIR__ . '/public/recife-supersimples.txt', $sqlSS);

echo "Individual: {$totalIndividual} registros\n";
echo "SuperSimples: {$totalSS} registros\n";
echo "Done\n";
