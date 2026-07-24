<?php
// Gera os arquivos feiradesantana-individual.txt e feiradesantana-supersimples.txt
// cidade_id=32, Feira de Santana - BA.
// Individual.pdf pág.27: só a 2ª tabela (Nosso Plano - Franquia, plano_id=1) — a 1ª tabela
// é o produto REFERÊNCIA (ANS obrigatório, sempre descartado) e foi ignorada conforme pedido.
// Nesta cidade não existe Nosso Médico (plano_id=23) nem Pleno.
// Super_Simples.pdf pág.20: só a tabela Super Simples (plano_id=5, COLS_6). Sem Nosso Médico (plano_id=24).
// Ambulatorial.pdf pág.33: plano_id=1 / acomodacao_id=3 (AMB), mesclado no bloco do plano_id=1.

$cidadeId = 32;
$now = '2026-07-16 12:00:00';

function money(float $v): string {
    return number_format($v, 2, '.', '');
}

// ── Individual.pdf pág.27 — 2ª tabela: NOSSO PLANO - FRANQUIA (plano_id=1), padrão MEDICO_COLS ──
$planoRows = [
    [290.73,295.58,436.10,440.95,263.56,268.41,395.34,400.19],
    [359.34,365.34,539.02,545.01,325.76,331.75,488.64,494.63],
    [413.24,420.14,619.87,626.76,374.62,381.51,561.94,568.82],
    [462.83,470.56,694.25,701.97,419.57,427.29,629.37,637.08],
    [485.97,494.09,728.96,737.07,440.55,448.65,660.84,668.93],
    [549.15,558.32,823.72,832.89,497.82,506.97,746.75,755.89],
    [682.98,694.38,1024.46,1035.87,619.14,630.52,928.73,940.10],
    [924.55,939.98,1386.81,1402.26,838.13,853.53,1257.22,1272.61],
    [1248.14,1268.97,1872.19,1893.05,1131.48,1152.27,1697.25,1718.02],
    [1575.28,1601.57,2362.89,2389.22,1428.04,1454.28,2142.10,2168.31],
];
$valorPromoIndividual = 3.85; // extraído da 1ª tabela (REFERÊNCIA), coluna VALOR PROMO, constante nas 10 linhas

// ── Ambulatorial.pdf pág.33 — plano_id=1, acomodacao_id=3 (AMB) ──
// [copat0-M1, copat0-M2, copat1-M1, copat1-M2] (coluna Referência ~10x maior já descartada)
$ambRows = [
    [270.16,275.01,171.57,176.42],
    [334.67,340.68,212.54,218.55],
    [382.19,389.06,242.72,249.58],
    [426.91,434.58,271.12,278.78],
    [449.54,457.61,285.49,293.56],
    [505.28,514.35,320.89,329.96],
    [630.99,642.32,400.73,412.05],
    [860.35,875.80,546.40,561.83],
    [1161.47,1182.33,737.64,758.47],
    [1465.89,1492.22,931.05,957.34],
];
$valorPromoAmb = 3.85; // valor da própria página do Ambulatorial.pdf, constante nas 10 linhas

// ── Super_Simples.pdf pág.20 — SUPER SIMPLES (plano_id=5), COLS_6 (única tabela da página) ──
// [AMB-copat0, ENF-copat0, APT-copat0, AMB-copat1, ENF-copat1, APT-copat1]
$ssPlanoRows = [
    [229.74,282.01,422.36,147.11,155.53,293.03],
    [257.31,315.85,473.04,164.76,174.19,328.19],
    [288.19,353.75,529.80,184.53,195.09,367.57],
    [331.42,406.81,609.27,212.21,224.35,422.71],
    [381.13,467.83,700.66,244.04,258.00,486.12],
    [453.54,556.72,833.79,290.41,307.02,578.48],
    [566.93,695.90,1042.24,363.01,383.78,723.10],
    [708.66,869.88,1302.80,453.76,479.73,903.88],
    [1204.72,1478.80,2214.76,771.39,815.54,1536.60],
    [1349.29,1656.26,2480.53,863.96,913.40,1720.99],
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

// ── Monta INDIVIDUAL.txt (só plano_id=1) ──
$planoData = buildMedicoColsShape($planoRows, $valorPromoIndividual);
$ambData = buildAmbuColsShape($ambRows, $valorPromoAmb);
foreach ($ambData as $copat => $byOdonto) {
    foreach ($byOdonto as $odonto => $byAcom) {
        $planoData[$copat][$odonto][ACOM_AMB] = $byAcom[ACOM_AMB];
    }
}

$allLinesIndividual = emitLines($planoData, $cidadeId, 1, $now);
$totalIndividual = count($allLinesIndividual);
$headerIndividual = <<<SQL
-- Importação Assistida: Feira de Santana - BA (individual, página 27)
-- cidade_id={$cidadeId} | plano_individual=1 | valorPromo=3.85
-- Página tem 2 tabelas: a 1ª é o produto REFERÊNCIA (REGISTRO ANS 436.062/01-1, ANS obrigatório) — descartada. A 2ª tabela ("Nosso Plano - Franquia", REGISTRO ANS 485.115/20-3 ENF / 485.116/20-1 APT) é a implementada aqui (plano_id=1, padrão MEDICO_COLS). Cidade não vende Nosso Médico (23) nem Pleno.
-- Ambulatorial (acomodacao_id=3) extraído de Ambulatorial.pdf página 33 (valorPromo=3.85, mesmo valor da própria página).
-- total registros={$totalIndividual}

SQL;

$sqlIndividual = $headerIndividual . "\nINSERT INTO `tabelas` (`administradora_id`, `tabela_origens_id`, `plano_id`, `acomodacao_id`, `faixa_etaria_id`, `coparticipacao`, `odonto`, `valor`, `created_at`, `updated_at`) VALUES\n"
    . implode(",\n", $allLinesIndividual) . ";\n";

file_put_contents(__DIR__ . '/public/feiradesantana-individual.txt', $sqlIndividual);

// ── Monta SUPERSIMPLES.txt (só plano_id=5) ──
$ssPlanoData = buildCols6Shape($ssPlanoRows, $valorPromoSS);

$allLinesSS = emitLines($ssPlanoData, $cidadeId, 5, $now);
$totalSS = count($allLinesSS);
$headerSS = <<<SQL
-- Importação Assistida: Feira de Santana - BA (super_simples, página 20)
-- cidade_id={$cidadeId} | plano_super_simples=5 | valorPromo=23.25
-- Página tem uma única tabela (Super Simples, plano_id=5, padrão COLS_6 com coluna S/ACOM). Cidade não vende Nosso Médico (24).
-- total registros={$totalSS}

SQL;

$sqlSS = $headerSS . "\nINSERT INTO `tabelas` (`administradora_id`, `tabela_origens_id`, `plano_id`, `acomodacao_id`, `faixa_etaria_id`, `coparticipacao`, `odonto`, `valor`, `created_at`, `updated_at`) VALUES\n"
    . implode(",\n", $allLinesSS) . ";\n";

file_put_contents(__DIR__ . '/public/feiradesantana-supersimples.txt', $sqlSS);

echo "Individual: {$totalIndividual} registros\n";
echo "SuperSimples: {$totalSS} registros\n";
echo "Done\n";
