<?php

use App\Support\CoparticipacaoCotacao;
use Illuminate\Support\Facades\DB;

/**
 * Coparticipações próprias da cotação ambulatorial (02/09/2026, caso Porto
 * Alegre/Super Simples): linha da tabela pdf com ambulatorial=1 vale só para a
 * cotação ambulatorial (com fallback para a linha normal); a cotação normal
 * usa SOMENTE a linha ambulatorial=0.
 */

function seedPdfCopar(): array
{
    $planoId  = DB::table('planos')->insertGetId(['nome' => 'Super Simples', 'created_at' => now(), 'updated_at' => now()]);
    $cidadeId = DB::table('tabela_origens')->insertGetId(['nome' => 'Porto Alegre', 'uf' => 'RS', 'created_at' => now(), 'updated_at' => now()]);
    return [$planoId, $cidadeId];
}

function linhaPdf(int $planoId, int $cidadeId, int $ambulatorial, string $consulta): void
{
    DB::table('pdf')->insert([
        'plano_id' => $planoId, 'tabela_origens_id' => $cidadeId,
        'ambulatorial' => $ambulatorial,
        'consultas_eletivas_total' => $consulta,
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

test('cotacao normal usa a linha normal mesmo existindo a ambulatorial', function () {
    [$planoId, $cidadeId] = seedPdfCopar();
    linhaPdf($planoId, $cidadeId, 0, '20,00');
    linhaPdf($planoId, $cidadeId, 1, '35,00');

    $copart = CoparticipacaoCotacao::montar($planoId, $cidadeId);

    expect($copart['pdf']->consultas_eletivas_total)->toBe('20,00');
});

test('cotacao ambulatorial prefere a linha ambulatorial quando cadastrada', function () {
    [$planoId, $cidadeId] = seedPdfCopar();
    linhaPdf($planoId, $cidadeId, 0, '20,00');
    linhaPdf($planoId, $cidadeId, 1, '35,00');

    $copart = CoparticipacaoCotacao::montar($planoId, $cidadeId, null, ambulatorial: true);

    expect($copart['pdf']->consultas_eletivas_total)->toBe('35,00');
});

test('cotacao ambulatorial sem linha propria cai na linha normal (fallback)', function () {
    [$planoId, $cidadeId] = seedPdfCopar();
    linhaPdf($planoId, $cidadeId, 0, '20,00');

    $copart = CoparticipacaoCotacao::montar($planoId, $cidadeId, null, ambulatorial: true);

    expect($copart['pdf']->consultas_eletivas_total)->toBe('20,00');
});

test('fallback por plano (sem linha da cidade) tambem respeita a preferencia', function () {
    [$planoId, $cidadeId] = seedPdfCopar();
    $outraCidade = DB::table('tabela_origens')->insertGetId(['nome' => 'Outra', 'uf' => 'SC', 'created_at' => now(), 'updated_at' => now()]);
    linhaPdf($planoId, $outraCidade, 0, '18,00'); // generica do plano, em outra cidade

    $normal = CoparticipacaoCotacao::montar($planoId, $cidadeId);
    $amb    = CoparticipacaoCotacao::montar($planoId, $cidadeId, null, ambulatorial: true);

    expect($normal['pdf']->consultas_eletivas_total)->toBe('18,00');
    expect($amb['pdf']->consultas_eletivas_total)->toBe('18,00');
});
