<?php

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;

/**
 * Barra de aviso de vencimento do PIX (18/10/2026): 3 dias antes do vencimento
 * o ADMINISTRADOR vê uma barra no topo com link para regularizar, para pagar
 * antes do bloqueio travar a equipe. Vendedor não vê (não é ele quem paga),
 * cartão não vê (cobrança automática) e fora da janela não aparece.
 */

function montarContaPix(array $assinatura = []): array
{
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
        'emails_extra'      => 1,
        'preco_total'       => 29.90,
        'status'            => 'ativo',
        'tipo'              => 'PIX',
        'next_charge'       => now()->addDays(2),
        'created_at'        => now(),
        'updated_at'        => now(),
    ], $assinatura));

    DB::table('emails_assinatura')->insert([
        'assinatura_id' => $assinaturaId, 'email' => $admin->email, 'user_id' => $admin->id,
        'is_administrador' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    return [$admin, $assinaturaId];
}

function renderizarBarra(): string
{
    return Blade::render('@include("layouts.alerta-vencimento")');
}

test('admin com PIX vencendo em 2 dias ve a barra com o link de regularizar', function () {
    [$admin] = montarContaPix();

    $this->actingAs($admin);
    $html = renderizarBarra();

    expect($html)->toContain('Sua assinatura vence')
        ->toContain('Regularizar agora')
        ->toContain(route('assinatura.edit'));
});

test('PIX vencendo hoje mostra HOJE', function () {
    [$admin] = montarContaPix(['next_charge' => now()->addHours(2)]);

    $this->actingAs($admin);

    expect(renderizarBarra())->toContain('HOJE');
});

test('PIX com vencimento longe nao mostra a barra', function () {
    [$admin] = montarContaPix(['next_charge' => now()->addDays(10)]);

    $this->actingAs($admin);

    expect(renderizarBarra())->not->toContain('Sua assinatura vence');
});

test('PIX ja vencido nao mostra a barra (ai o bloqueio age)', function () {
    [$admin] = montarContaPix(['next_charge' => now()->subDay()]);

    $this->actingAs($admin);

    expect(renderizarBarra())->not->toContain('Sua assinatura vence');
});

test('assinatura de cartao nao mostra a barra', function () {
    [$admin] = montarContaPix(['tipo' => 'cartao', 'next_charge' => now()->addDays(2)]);

    $this->actingAs($admin);

    expect(renderizarBarra())->not->toContain('Sua assinatura vence');
});

test('vendedor da equipe nao ve a barra, so o admin', function () {
    [, $assinaturaId] = montarContaPix();

    $vendedor = User::factory()->create(['layout_id' => null]);
    DB::table('emails_assinatura')->insert([
        'assinatura_id' => $assinaturaId, 'email' => $vendedor->email, 'user_id' => $vendedor->id,
        'is_administrador' => false, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($vendedor);

    expect(renderizarBarra())->not->toContain('Sua assinatura vence');
});

test('PIX_AUTOMATICO na janela tambem mostra a barra', function () {
    [$admin] = montarContaPix(['tipo' => 'PIX_AUTOMATICO', 'next_charge' => now()->addDay()]);

    $this->actingAs($admin);

    expect(renderizarBarra())->toContain('Sua assinatura vence');
});
