<?php

use App\Models\Assinatura;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Bloqueio de assinaturas de cartão inadimplentes (16/10/2026).
 *
 * O caso real: gabrielmanrick972@gmail.com, tipo 'CARTAO' (maiúsculo, gravado pelo
 * fluxo trial→cartão), não pagou a fatura de 13/10 e continuava usando o sistema,
 * porque o middleware comparava tipo === 'cartao' (case-sensitive). A regra agora
 * vive em Assinatura::cartaoInadimplente() e vale para as duas grafias, com
 * carência de Assinatura::CARENCIA_CARTAO_DIAS dias para as retentativas da Efi.
 */

beforeEach(function () {
    Route::middleware(['web', 'auth', 'check'])->get('/_teste-check', fn () => 'liberado');
});

function criarAssinaturaCartao(array $assinatura = [], ?User $membro = null): array
{
    $admin = User::factory()->create(['layout_id' => null]);

    static $tipoPlanoId = null;
    if ($tipoPlanoId === null || ! DB::table('tipos_planos')->where('id', $tipoPlanoId)->exists()) {
        $tipoPlanoId = DB::table('tipos_planos')->insertGetId([
            'nome' => 'Individual', 'valor_base' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $assinaturaId = DB::table('assinaturas')->insertGetId(array_merge([
        'user_id'         => $admin->id,
        'tipo_plano_id'   => $tipoPlanoId,
        'preco_base'      => 29.90,
        'preco_total'     => 269.10,
        'status'          => 'ativo',
        'tipo'            => 'CARTAO',
        'subscription_id' => 1408291,
        'created_at'      => now(),
        'updated_at'      => now(),
    ], $assinatura));

    foreach (array_filter([$admin, $membro]) as $usuario) {
        DB::table('emails_assinatura')->insert([
            'assinatura_id'    => $assinaturaId,
            'email'            => $usuario->email,
            'user_id'          => $usuario->id,
            'is_administrador' => $usuario->is($admin),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    return [$admin, $assinaturaId];
}

test('admin com CARTAO vencido alem da carencia e mandado renovar', function () {
    [$admin] = criarAssinaturaCartao([
        'next_charge' => now()->subDays(Assinatura::CARENCIA_CARTAO_DIAS + 1),
    ]);

    $this->actingAs($admin)->get('/_teste-check')
        ->assertRedirect(route('assinatura.edit'));
});

test('cartao minusculo vencido tambem bloqueia', function () {
    [$admin] = criarAssinaturaCartao([
        'tipo'        => 'cartao',
        'next_charge' => now()->subDays(Assinatura::CARENCIA_CARTAO_DIAS + 1),
    ]);

    $this->actingAs($admin)->get('/_teste-check')
        ->assertRedirect(route('assinatura.edit'));
});

test('cartao vencido dentro da carencia continua liberado', function () {
    [$admin] = criarAssinaturaCartao(['next_charge' => now()->subDay()]);

    $this->actingAs($admin)->get('/_teste-check')->assertOk();
});

test('cartao em dia continua liberado', function () {
    [$admin] = criarAssinaturaCartao(['next_charge' => now()->addDays(20)]);

    $this->actingAs($admin)->get('/_teste-check')->assertOk();
});

test('cartao com status inativo bloqueia mesmo sem vencimento', function () {
    [$admin] = criarAssinaturaCartao([
        'status'      => 'inativo',
        'next_charge' => now()->addDays(20),
    ]);

    $this->actingAs($admin)->get('/_teste-check')
        ->assertRedirect(route('assinatura.edit'));
});

test('membro da equipe de cartao vencido vai para a pagina de expirada, sem loop', function () {
    $membro = User::factory()->create(['layout_id' => null]);
    criarAssinaturaCartao(
        ['next_charge' => now()->subDays(Assinatura::CARENCIA_CARTAO_DIAS + 1)],
        $membro
    );

    $this->actingAs($membro)->get('/_teste-check')
        ->assertRedirect(route('assinatura.expirada'));

    // A página de expirada reconhece cartão inadimplente (antes só trial/PIX, o que
    // devolvia a equipe ao dashboard e criava um loop infinito de redirects).
    $this->actingAs($membro)->get(route('assinatura.expirada'))
        ->assertOk()
        ->assertSee('Acesso Bloqueado');
});

test('pix vencido segue bloqueando como antes', function () {
    [$admin] = criarAssinaturaCartao([
        'tipo'            => 'PIX',
        'subscription_id' => null,
        'next_charge'     => now()->subDay(),
    ]);

    $this->actingAs($admin)->get('/_teste-check')
        ->assertRedirect(route('assinatura.edit'));
});

test('pix em dia segue liberado', function () {
    [$admin] = criarAssinaturaCartao([
        'tipo'            => 'PIX',
        'subscription_id' => null,
        'next_charge'     => now()->addDays(10),
    ]);

    $this->actingAs($admin)->get('/_teste-check')->assertOk();
});
