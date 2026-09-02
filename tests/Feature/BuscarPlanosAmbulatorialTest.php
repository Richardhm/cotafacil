<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Regra dinâmica do Ambulatorial (01/09/2026, caso Porto Alegre id 63):
 * TODO plano da operadora+cidade+assinatura com valor ambulatorial cadastrado
 * (acomodacao_id=3, valor>0) entra em planos_ambulatoriais — não só o Individual.
 * tem_ambulatorial/plano_ambulatorial_id continuam na resposta por compatibilidade.
 */

function montarMundo(): array
{
    $user = User::factory()->create(['layout_id' => null]);

    $tipoPlanoId = DB::table('tipos_planos')->insertGetId([
        'nome' => 'Individual', 'valor_base' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $assinaturaId = DB::table('assinaturas')->insertGetId([
        'user_id' => $user->id, 'tipo_plano_id' => $tipoPlanoId,
        'preco_base' => 0, 'preco_total' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('emails_assinatura')->insert([
        'assinatura_id' => $assinaturaId, 'email' => $user->email,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $adminId  = DB::table('administradoras')->insertGetId(['nome' => 'Hapvida', 'logo' => 'h.png', 'created_at' => now(), 'updated_at' => now()]);
    $cidadeId = DB::table('tabela_origens')->insertGetId(['nome' => 'Porto Alegre', 'uf' => 'RS', 'created_at' => now(), 'updated_at' => now()]);

    foreach (['Apartamento', 'Enfermaria', 'Ambulatorial'] as $nome) {
        DB::table('acomodacoes')->insert(['nome' => $nome, 'created_at' => now(), 'updated_at' => now()]);
    }
    $faixaId = DB::table('faixa_etarias')->insertGetId(['nome' => '00 a 18 anos', 'created_at' => now(), 'updated_at' => now()]);

    $planos = [];
    foreach (['Individual', 'Super Simples', 'PME'] as $nome) {
        $planoId = DB::table('planos')->insertGetId(['nome' => $nome, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('administradora_planos')->insert([
            'plano_id' => $planoId, 'administradora_id' => $adminId,
            'tabela_origens_id' => $cidadeId, 'assinatura_id' => $assinaturaId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $planos[$nome] = $planoId;
    }

    return compact('user', 'assinaturaId', 'adminId', 'cidadeId', 'faixaId', 'planos');
}

function precoTabela(array $m, int $planoId, int $acomodacaoId, float $valor): void
{
    DB::table('tabelas')->insert([
        'administradora_id' => $m['adminId'], 'tabela_origens_id' => $m['cidadeId'],
        'plano_id' => $planoId, 'acomodacao_id' => $acomodacaoId,
        'faixa_etaria_id' => $m['faixaId'], 'coparticipacao' => 1, 'odonto' => 1,
        'valor' => $valor, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

function buscarPlanos(array $m)
{
    return test()->actingAs($m['user'])->post('/buscar_planos', [
        'administradora_id' => $m['adminId'],
        'tabela_origens_id' => $m['cidadeId'],
    ]);
}

test('caso Porto Alegre: Individual E Super Simples com ambulatorial aparecem os dois', function () {
    $m = montarMundo();
    precoTabela($m, $m['planos']['Individual'], 1, 126.57);
    precoTabela($m, $m['planos']['Individual'], 3, 90.00);      // ambulatorial
    precoTabela($m, $m['planos']['Super Simples'], 1, 110.00);
    precoTabela($m, $m['planos']['Super Simples'], 3, 75.00);   // ambulatorial
    precoTabela($m, $m['planos']['PME'], 1, 140.00);            // PME sem ambulatorial

    $response = buscarPlanos($m);
    $response->assertOk();

    $amb = collect($response->json('planos_ambulatoriais'));
    expect($amb)->toHaveCount(2);
    expect($amb->pluck('nome')->all())->toContain('Individual', 'Super Simples');
    expect($amb->pluck('nome')->all())->not->toContain('PME');
    expect($response->json('tem_ambulatorial'))->toBeTrue();
    expect($response->json('plano_ambulatorial_id'))->not->toBeNull();
});

test('cidade comum: so o Individual tem ambulatorial e so ele aparece', function () {
    $m = montarMundo();
    precoTabela($m, $m['planos']['Individual'], 1, 126.57);
    precoTabela($m, $m['planos']['Individual'], 3, 90.00);
    precoTabela($m, $m['planos']['Super Simples'], 1, 110.00);

    $response = buscarPlanos($m);

    $amb = collect($response->json('planos_ambulatoriais'));
    expect($amb)->toHaveCount(1);
    expect($amb->first()['nome'])->toBe('Individual');
    expect($response->json('plano_ambulatorial_id'))->toBe($m['planos']['Individual']);
});

test('cidade sem nenhum ambulatorial: lista vazia e tem_ambulatorial false', function () {
    $m = montarMundo();
    precoTabela($m, $m['planos']['Individual'], 1, 126.57);
    precoTabela($m, $m['planos']['Super Simples'], 2, 95.00);

    $response = buscarPlanos($m);

    expect($response->json('planos_ambulatoriais'))->toBe([]);
    expect($response->json('tem_ambulatorial'))->toBeFalse();
    expect($response->json('plano_ambulatorial_id'))->toBeNull();
});

test('ambulatorial com valor zero nao conta', function () {
    $m = montarMundo();
    precoTabela($m, $m['planos']['Individual'], 3, 90.00);
    precoTabela($m, $m['planos']['Super Simples'], 3, 0.00); // cadastrado mas zerado

    $response = buscarPlanos($m);

    $amb = collect($response->json('planos_ambulatoriais'));
    expect($amb)->toHaveCount(1);
    expect($amb->first()['nome'])->toBe('Individual');
});

test('plano com ambulatorial mas fora da assinatura do usuario nao aparece', function () {
    $m = montarMundo();
    precoTabela($m, $m['planos']['Individual'], 3, 90.00);

    // Plano "Fantasma" tem preço ambulatorial na cidade mas NÃO está vinculado à assinatura
    $fantasmaId = DB::table('planos')->insertGetId(['nome' => 'Fantasma', 'created_at' => now(), 'updated_at' => now()]);
    precoTabela($m, $fantasmaId, 3, 60.00);

    $response = buscarPlanos($m);

    $amb = collect($response->json('planos_ambulatoriais'));
    expect($amb->pluck('nome')->all())->toBe(['Individual']);
});
