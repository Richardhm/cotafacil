<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * /tabela_completa (03/09/2026): a "Tabela de Origem" virou UF -> Cidade como no
 * dashboard, montada SÓ com os vínculos (administradora_planos) da assinatura do
 * usuário — antes o select listava TabelaOrigens::all(), todas as cidades.
 */

function mundoTabelaCompleta(): array
{
    $user = User::factory()->create(['layout_id' => null]);

    $tipoPlanoId = DB::table('tipos_planos')->insertGetId([
        'nome' => 'Individual', 'valor_base' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $assinaturaId = DB::table('assinaturas')->insertGetId([
        'user_id' => $user->id, 'tipo_plano_id' => $tipoPlanoId,
        'preco_base' => 0, 'preco_total' => 0, 'status' => 'ativo',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('emails_assinatura')->insert([
        'assinatura_id' => $assinaturaId, 'email' => $user->email, 'is_administrador' => true,
        'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $adminId = DB::table('administradoras')->insertGetId(['nome' => 'Hapvida', 'logo' => 'h.png', 'created_at' => now(), 'updated_at' => now()]);
    $planoId = DB::table('planos')->insertGetId(['nome' => 'Individual', 'created_at' => now(), 'updated_at' => now()]);

    return compact('user', 'assinaturaId', 'adminId', 'planoId', 'tipoPlanoId');
}

function cidadeVinculada(array $m, string $nome, string $uf, ?int $assinaturaId = null): int
{
    $cidadeId = DB::table('tabela_origens')->insertGetId([
        'nome' => $nome, 'uf' => $uf, 'descricao' => 'Estado ' . $uf,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('administradora_planos')->insert([
        'plano_id' => $m['planoId'], 'administradora_id' => $m['adminId'],
        'tabela_origens_id' => $cidadeId, 'assinatura_id' => $assinaturaId ?? $m['assinaturaId'],
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return $cidadeId;
}

test('mostra UF e cidades so dos vinculos da assinatura do usuario', function () {
    $m = mundoTabelaCompleta();
    cidadeVinculada($m, 'Fortaleza', 'CE');
    cidadeVinculada($m, 'Juazeiro do Norte', 'CE');
    cidadeVinculada($m, 'Porto Alegre', 'RS');

    // Cidade de OUTRA assinatura: não pode aparecer
    $outroUser = User::factory()->create(['layout_id' => null]);
    $outraAssinatura = DB::table('assinaturas')->insertGetId([
        'user_id' => $outroUser->id, 'tipo_plano_id' => $m['tipoPlanoId'],
        'preco_base' => 0, 'preco_total' => 0, 'status' => 'ativo',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    cidadeVinculada($m, 'Manaus', 'AM', $outraAssinatura);

    $response = $this->actingAs($m['user'])->get('/tabela_completa');

    $response->assertOk();
    // UFs dos vínculos no select
    $response->assertSee('CE - Estado CE');
    $response->assertSee('RS - Estado RS');
    $response->assertDontSee('AM - Estado AM');
    // Cidades no mapa JSON do cascateamento
    $response->assertSee('Fortaleza');
    $response->assertSee('Juazeiro do Norte');
    $response->assertSee('Porto Alegre');
    $response->assertDontSee('Manaus');
});

test('uf preferida do usuario ja vem selecionada', function () {
    $m = mundoTabelaCompleta();
    cidadeVinculada($m, 'Fortaleza', 'CE');
    cidadeVinculada($m, 'Porto Alegre', 'RS');

    $m['user']->forceFill(['uf_preferencia' => 'RS'])->save();

    $response = $this->actingAs($m['user'])->get('/tabela_completa');

    $response->assertOk();
    $response->assertSee('value="RS" selected', false);
});
