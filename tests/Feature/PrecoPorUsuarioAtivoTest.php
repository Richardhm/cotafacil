<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Preço da assinatura acompanha os usuários ATIVOS (16/10/2026).
 *
 * Regra do Richard: toda vez que um vendedor é desativado o sistema tem que
 * descontar R$ 29,90 da assinatura (e somar de volta ao reativar), valendo para
 * PIX e cartão. O recálculo é feito pela CONTAGEM real de ativos (o ±1 antigo
 * acumulava erro: a equipe do Gabriel tinha 4 desativados e emails_extra 9).
 * O espelhamento do valor na Efi (cartão) é testado com subscription_id nulo,
 * que pula a chamada externa — a lógica fica em sincronizarValorCartaoEfi.
 */

function montarEquipe(int $vendedoresAtivos, array $assinatura = []): array
{
    // users.layout_id tem DEFAULT 1 com FK para layouts: o storeUser cria o usuário
    // sem informar layout_id, então a linha 1 precisa existir no banco de teste.
    if (! DB::table('layouts')->where('id', 1)->exists()) {
        DB::table('layouts')->insert([
            'id' => 1, 'nome' => 'Modelo 1', 'imagem' => 'layouts/modelo1.png',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    if (! DB::table('tipos_planos')->where('id', 1)->exists()) {
        DB::table('tipos_planos')->insert([
            'id' => 1, 'nome' => 'Individual', 'valor_base' => 29.90,
            'valor_por_email' => 29.90, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $admin = User::factory()->create(['layout_id' => null]);

    $assinaturaId = DB::table('assinaturas')->insertGetId(array_merge([
        'user_id'           => $admin->id,
        'tipo_plano_id'     => 1,
        'preco_base'        => 29.90,
        'emails_permitidos' => 1,
        // Valores propositalmente errados para provar que o recálculo é por contagem:
        'emails_extra'      => 99,
        'preco_total'       => 999.99,
        'status'            => 'ativo',
        'tipo'              => 'CARTAO',
        'subscription_id'   => null,
        'next_charge'       => now()->addMonth(),
        'created_at'        => now(),
        'updated_at'        => now(),
    ], $assinatura));

    DB::table('emails_assinatura')->insert([
        'assinatura_id' => $assinaturaId, 'email' => $admin->email, 'user_id' => $admin->id,
        'is_administrador' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $vendedores = [];
    for ($i = 0; $i < $vendedoresAtivos; $i++) {
        $vendedor = User::factory()->create(['layout_id' => null]);
        DB::table('emails_assinatura')->insert([
            'assinatura_id' => $assinaturaId, 'email' => $vendedor->email, 'user_id' => $vendedor->id,
            'is_administrador' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $vendedores[] = $vendedor;
    }

    return [$admin, $vendedores, $assinaturaId];
}

test('desativar vendedor desconta 29,90 e corrige o emails_extra pela contagem', function () {
    [$admin, $vendedores, $assinaturaId] = montarEquipe(3); // admin + 3 ativos = 119,60

    $this->actingAs($admin)
        ->post(route('users.alterar'), ['userId' => $vendedores[0]->id, 'status' => 'false'])
        ->assertOk()
        ->assertJson(['success' => true, 'emails_extra' => 3, 'emails_cobrados' => 2]);

    $assinatura = DB::table('assinaturas')->find($assinaturaId);
    expect((float) $assinatura->preco_total)->toBe(89.70)   // 29,90 + 2 × 29,90
        ->and((int) $assinatura->emails_extra)->toBe(3)
        ->and((int) $vendedores[0]->fresh()->status)->toBe(0);
});

test('reativar vendedor soma 29,90 de volta', function () {
    [$admin, $vendedores, $assinaturaId] = montarEquipe(3);
    // status não é fillable no User: desativa via DB direto
    DB::table('users')->where('id', $vendedores[0]->id)->update(['status' => 0]);

    $this->actingAs($admin)
        ->post(route('users.alterar'), ['userId' => $vendedores[0]->id, 'status' => 'true'])
        ->assertOk()
        ->assertJson(['success' => true, 'emails_extra' => 4, 'emails_cobrados' => 3]);

    expect((float) DB::table('assinaturas')->find($assinaturaId)->preco_total)->toBe(119.60);
});

test('so com o admin ativo o preco nunca cai abaixo de 29,90', function () {
    [$admin, $vendedores, $assinaturaId] = montarEquipe(1);

    $this->actingAs($admin)
        ->post(route('users.alterar'), ['userId' => $vendedores[0]->id, 'status' => 'false'])
        ->assertOk();

    expect((float) DB::table('assinaturas')->find($assinaturaId)->preco_total)->toBe(29.90);
});

test('admin nao consegue se auto-desativar', function () {
    [$admin] = montarEquipe(1);

    $this->actingAs($admin)
        ->post(route('users.alterar'), ['userId' => $admin->id, 'status' => 'false'])
        ->assertStatus(403);
});

test('assinatura free nao tem o preco alterado', function () {
    [$admin, $vendedores, $assinaturaId] = montarEquipe(2, ['free' => 1, 'preco_total' => 0]);

    $this->actingAs($admin)
        ->post(route('users.alterar'), ['userId' => $vendedores[0]->id, 'status' => 'false'])
        ->assertOk();

    expect((float) DB::table('assinaturas')->find($assinaturaId)->preco_total)->toBe(0.0)
        ->and((int) $vendedores[0]->fresh()->status)->toBe(0);
});

test('adicionar vendedor pelo gerenciamento soma 29,90 pela contagem de ativos', function () {
    [$admin, , $assinaturaId] = montarEquipe(1); // admin + 1 ativo

    $this->actingAs($admin)
        ->post(route('storeUser'), [
            'name'  => 'Vendedor Novo',
            'email' => 'vendedor.novo@example.com',
            'phone' => '(62) 99999-0001',
        ])
        ->assertOk()
        ->assertJson(['success' => true, 'emails_extra' => 3, 'emails_cobrados' => 2]);

    expect((float) DB::table('assinaturas')->find($assinaturaId)->preco_total)->toBe(89.70);
});

test('financeiro mostra Ativado/Desativado em cada card de usuario', function () {
    [, $vendedores] = montarEquipe(2);
    DB::table('users')->where('id', $vendedores[0]->id)->update(['status' => 0]);

    $dev = User::factory()->create(['layout_id' => null, 'email' => 'richardjonhshm@gmail.com']);

    $this->actingAs($dev)->get('/financeiro')
        ->assertOk()
        ->assertSee('Ativado')
        ->assertSee('Desativado');
});

test('comando assinaturas:recalcular-ativos corrige o drift e o dry-run nao grava nada', function () {
    // admin + 3 vendedores, 1 desativado = 3 ativos; valores de partida errados (99 / 999,99)
    [, $vendedores, $assinaturaId] = montarEquipe(3);
    DB::table('users')->where('id', $vendedores[0]->id)->update(['status' => 0]);

    // free não pode ser tocada pelo comando
    [, , $assinaturaFreeId] = montarEquipe(1, ['free' => 1, 'preco_total' => 0]);

    $this->artisan('assinaturas:recalcular-ativos', ['--dry-run' => true])->assertExitCode(0);
    $a = DB::table('assinaturas')->find($assinaturaId);
    expect((float) $a->preco_total)->toBe(999.99)
        ->and((int) $a->emails_extra)->toBe(99);

    $this->artisan('assinaturas:recalcular-ativos')->assertExitCode(0);
    $a = DB::table('assinaturas')->find($assinaturaId);
    expect((float) $a->preco_total)->toBe(89.70)   // 29,90 + 2 × 29,90
        ->and((int) $a->emails_extra)->toBe(3)
        ->and((float) DB::table('assinaturas')->find($assinaturaFreeId)->preco_total)->toBe(0.0);
});

test('preco_fixo (combinado) mantem o preco no toggle e no comando, mas corrige a contagem', function () {
    [$admin, $vendedores, $assinaturaId] = montarEquipe(3, ['preco_fixo' => 1, 'preco_total' => 109.90]);

    $this->actingAs($admin)
        ->post(route('users.alterar'), ['userId' => $vendedores[0]->id, 'status' => 'false'])
        ->assertOk();

    $a = DB::table('assinaturas')->find($assinaturaId);
    expect((float) $a->preco_total)->toBe(109.90)     // preço combinado intacto
        ->and((int) $a->emails_extra)->toBe(3);       // contagem corrigida (99 → 3 ativos)

    $this->artisan('assinaturas:recalcular-ativos')->assertExitCode(0);
    expect((float) DB::table('assinaturas')->find($assinaturaId)->preco_total)->toBe(109.90);
});

test('remover vendedor da equipe recalcula o preco', function () {
    [$admin, $vendedores, $assinaturaId] = montarEquipe(3); // 119,60

    $this->actingAs($admin)
        ->post(route('deletar.user'), ['id' => $vendedores[0]->id])
        ->assertOk();

    $assinatura = DB::table('assinaturas')->find($assinaturaId);
    expect((float) $assinatura->preco_total)->toBe(89.70)
        ->and((int) $assinatura->emails_extra)->toBe(3);
});
