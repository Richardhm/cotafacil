<?php

use App\Http\Controllers\VendasController;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Página /vendas (02/09/2026): vendedora (Marya) + desenvolvedores veem as
 * assinaturas cadastradas a partir de VendasController::INICIO_VENDAS (20/08/2026).
 * Acesso controlado pelo middleware ApenasVendas (lista de e-mails NO middleware —
 * User.php não pode ser tocado, é o arquivo divergente de produção).
 */

function criarAssinaturaVendas(User $admin, string $criadaEm, string $status = 'ativo', float $preco = 99.90): int
{
    static $tipoPlanoId = null;
    if ($tipoPlanoId === null || ! DB::table('tipos_planos')->where('id', $tipoPlanoId)->exists()) {
        $tipoPlanoId = DB::table('tipos_planos')->insertGetId([
            'nome' => 'Individual', 'valor_base' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $assinaturaId = DB::table('assinaturas')->insertGetId([
        'user_id' => $admin->id, 'tipo_plano_id' => $tipoPlanoId,
        'preco_base' => $preco, 'preco_total' => $preco, 'status' => $status,
        'created_at' => $criadaEm, 'updated_at' => $criadaEm,
    ]);
    DB::table('emails_assinatura')->insert([
        'assinatura_id' => $assinaturaId, 'email' => $admin->email,
        'is_administrador' => true,
        'user_id' => $admin->id,
        'created_at' => $criadaEm, 'updated_at' => $criadaEm,
    ]);

    return $assinaturaId;
}

test('vendedora acessa a pagina', function () {
    $marya = User::factory()->create(['layout_id' => null, 'email' => 'maryaeduardaaccert@gmail.com']);

    $this->actingAs($marya)->get('/vendas')->assertOk()->assertSee('Vendas');
});

test('desenvolvedor acessa a pagina', function () {
    $dev = User::factory()->create(['layout_id' => null, 'email' => 'richardjonhshm@gmail.com']);

    $this->actingAs($dev)->get('/vendas')->assertOk();
});

test('usuario comum recebe 403', function () {
    $user = User::factory()->create(['layout_id' => null]);

    $this->actingAs($user)->get('/vendas')->assertForbidden();
});

test('visitante e mandado para o login', function () {
    $this->get('/vendas')->assertRedirect('/login');
});

test('mostra so as assinaturas cadastradas a partir de 20/08/2026', function () {
    $marya = User::factory()->create(['layout_id' => null, 'email' => 'maryaeduardaaccert@gmail.com']);

    $antigo = User::factory()->create(['layout_id' => null, 'name' => 'Cliente Antigo']);
    $novo   = User::factory()->create(['layout_id' => null, 'name' => 'Cliente Novo']);

    criarAssinaturaVendas($antigo, '2026-08-10 10:00:00');
    criarAssinaturaVendas($novo, '2026-08-25 10:00:00');

    $response = $this->actingAs($marya)->get('/vendas');

    $response->assertOk();
    $response->assertSee('Cliente Novo');
    $response->assertDontSee('Cliente Antigo');
});

test('cards contam certo por status', function () {
    $marya = User::factory()->create(['layout_id' => null, 'email' => 'maryaeduardaaccert@gmail.com']);

    criarAssinaturaVendas(User::factory()->create(['layout_id' => null]), '2026-08-25 10:00:00', 'ativo', 100.00);
    criarAssinaturaVendas(User::factory()->create(['layout_id' => null]), '2026-08-26 10:00:00', 'ativo', 50.00);
    criarAssinaturaVendas(User::factory()->create(['layout_id' => null]), '2026-08-27 10:00:00', 'trial', 0.00);

    $response = $this->actingAs($marya)->get('/vendas');

    $response->assertOk();
    expect($response->viewData('totalNovas'))->toBe(3);
    // Usuários DAS contas novas (a própria Marya, criada hoje, não conta)
    expect($response->viewData('totalUsuarios'))->toBe(3);
    expect($response->viewData('totalAtivas'))->toBe(2);
    expect($response->viewData('totalTrial'))->toBe(1);
    expect((float) $response->viewData('receita'))->toBe(150.00);
});
