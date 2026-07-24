<?php
// Gera o arquivo quirinopolis-supersimples.txt
// cidade_id=80, Quirinópolis - GO.
// Super_Simples.pdf pág.30: layout "Integrado + Pleno" (2 tabelas, sem Nosso Médico separado).
// Tabela 1 = INTEGRADO (plano_id=10), só ENFERMARIA, 2 valores/linha [ENF-copat0, ENF-copat1].
// Tabela 2 = PLENO (plano_id=11), ENFERMARIA+APARTAMENTO, 4 valores/linha [ENF-copat0, APT-copat0, ENF-copat1, APT-copat1].
// odonto=1 é sempre derivado (+valorPromo); o PDF só mostra odonto=0. Integrado não vende Apartamento (0.00).

$cidadeId = 80;
$now = '2026-07-16 12:00:00';

function money(float $v): string {
    return number_format($v, 2, '.', '');
}

// ── Tabela 1: INTEGRADO (plano_id=10) — [ENF-copat0, ENF-copat1] ──
$integradoRows = [
    [251.73,138.29],
    [261.80,143.82],
    [261.80,143.82],
    [301.07,165.39],
    [346.23,190.20],
    [402.18,220.94],
    [619.36,340.25],
    [947.62,520.58],
    [1065.79,585.50],
    [1509.16,829.07],
];

// ── Tabela 2: PLENO (plano_id=11) — [ENF-copat0, APT-copat0, ENF-copat1, APT-copat1] ──
$plenoRows = [
    [326.64,424.10,179.52,233.14],
    [339.71,441.06,186.70,242.47],
    [339.71,441.06,186.70,242.47],
    [390.67,507.22,214.71,278.84],
    [449.27,583.30,246.92,320.67],
    [521.87,677.56,286.82,372.49],
    [803.68,1043.44,441.70,573.63],
    [1229.63,1596.46,675.80,877.65],
    [1382.96,1795.54,760.07,987.09],
    [1958.27,2542.48,1076.26,1397.72],
];

$valorPromo = 23.25;

const ACOM_APT = 1;
const ACOM_ENF = 2;
const ACOM_AMB = 3;

function buildIntegradoShape(array $rows, float $valorPromo): array {
    // rows[f] = [ENF-copat0, ENF-copat1]; só Enfermaria (Apartamento fica 0.00 via default)
    $defs = [0 => 0, 1 => 1]; // colIdx => copat
    $out = [];
    foreach ($defs as $colIdx => $copat) {
        foreach ([1, 0] as $odonto) {
            for ($f = 0; $f < 10; $f++) {
                $valor = $rows[$f][$colIdx];
                if ($odonto === 1 && $valorPromo > 0) {
                    $valor = round($valor + $valorPromo, 2);
                }
                $out[$copat][$odonto][ACOM_ENF][$f + 1] = round($valor, 2);
            }
        }
    }
    return $out;
}

function buildPlenoShape(array $rows, float $valorPromo): array {
    // rows[f] = [ENF-copat0, APT-copat0, ENF-copat1, APT-copat1]
    $defs = [
        0 => ['copat'=>0,'acom'=>ACOM_ENF],
        1 => ['copat'=>0,'acom'=>ACOM_APT],
        2 => ['copat'=>1,'acom'=>ACOM_ENF],
        3 => ['copat'=>1,'acom'=>ACOM_APT],
    ];
    $out = [];
    foreach ($defs as $colIdx => $def) {
        foreach ([1, 0] as $odonto) {
            for ($f = 0; $f < 10; $f++) {
                $valor = $rows[$f][$colIdx];
                if ($odonto === 1 && $valorPromo > 0) {
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

$integradoData = buildIntegradoShape($integradoRows, $valorPromo);
$plenoData     = buildPlenoShape($plenoRows, $valorPromo);

$linesIntegrado = emitLines($integradoData, $cidadeId, 10, $now);
$linesPleno     = emitLines($plenoData, $cidadeId, 11, $now);
$allLines = array_merge($linesIntegrado, $linesPleno);

$total = count($allLines);
$header = <<<SQL
-- Importação Assistida: Quirinópolis - GO (super_simples, página 30)
-- cidade_id={$cidadeId} | plano_integrado=10 | plano_pleno=11 | valorPromo=23.25
-- Layout "Integrado + Pleno": Tabela 1 = INTEGRADO (plano_id=10, só Enfermaria, Apartamento=0.00). Tabela 2 = PLENO (plano_id=11, Enfermaria+Apartamento). odonto=1 derivado (+valorPromo) — o PDF só traz odonto=0. Sem Nosso Médico separado nesta página.
-- total registros={$total}

SQL;

$sql = $header . "\nINSERT INTO `tabelas` (`administradora_id`, `tabela_origens_id`, `plano_id`, `acomodacao_id`, `faixa_etaria_id`, `coparticipacao`, `odonto`, `valor`, `created_at`, `updated_at`) VALUES\n"
    . implode(",\n", $allLines) . ";\n";

file_put_contents(__DIR__ . '/public/quirinopolis-supersimples.txt', $sql);

echo "SuperSimples: {$total} registros\n";
echo "Done\n";
