<?php

namespace Tests\Feature;

use App\Models\Assinatura;
use App\Models\EmailAssinatura;
use App\Models\Humana\HumanaAcesso;
use App\Models\Humana\HumanaPreco;
use App\Models\Humana\HumanaTabela;
use App\Models\User;
use Database\Seeders\HumanaFaixaEtariasSeeder;
use Database\Seeders\HumanaPlanosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Módulo Humana (Teresina-PI): controle de acesso, endpoint de preços,
 * importador e geração de documento. Roda no sqlite em memória do phpunit.xml.
 */
class HumanaModuleTest extends TestCase
{
    use RefreshDatabase;

    /** Usuário comum com assinatura válida (trial vigente) — passa no middleware 'check'. */
    private function usuarioComAssinatura(): User
    {
        // layout_id null: o default 1 aponta FK para layouts, vazia no banco de teste
        $user = User::factory()->create(['layout_id' => null]);
        $tipoPlanoId = \Illuminate\Support\Facades\DB::table('tipos_planos')->insertGetId([
            'nome'       => 'Teste',
            'valor_base' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $assinatura = Assinatura::create([
            'user_id'       => $user->id,
            'tipo_plano_id' => $tipoPlanoId,
            'preco_base'    => 100,
            'preco_total'   => 100,
            'status'        => 'trial',
            'trial_ends_at' => now()->addDays(7),
        ]);
        EmailAssinatura::create([
            'assinatura_id'    => $assinatura->id,
            'email'            => $user->email,
            'user_id'          => $user->id,
            'is_administrador' => 1,
        ]);

        return $user;
    }

    private function liberarHumana(User $user, bool $ativo = true): void
    {
        // Liberação POR USUÁRIO (não por assinatura — equipe não herda)
        HumanaAcesso::updateOrCreate(['user_id' => $user->id], ['ativo' => $ativo]);
    }

    /** Catálogo completo: seeders + importação do JSON real versionado no repo. */
    private function prepararCatalogo(): void
    {
        $this->seed(HumanaFaixaEtariasSeeder::class);
        $this->seed(HumanaPlanosSeeder::class);
        Artisan::call('humana:importar', ['arquivo' => 'teresina-2026-08.json']);
    }

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $this->get('/humanas')->assertRedirect('/login');
    }

    public function test_usuario_sem_liberacao_recebe_403_em_todas_as_rotas(): void
    {
        $user = $this->usuarioComAssinatura();

        $this->actingAs($user)->get('/humanas')->assertForbidden();
        $this->actingAs($user)->post('/humanas/precos', ['plano_id' => 1])->assertForbidden();
        $this->actingAs($user)->post('/humanas/gerar', [])->assertForbidden();
    }

    public function test_usuario_liberado_acessa_e_recebe_precos_corretos(): void
    {
        $this->prepararCatalogo();
        $user = $this->usuarioComAssinatura();
        $this->liberarHumana($user);

        $this->actingAs($user)->get('/humanas')->assertOk()->assertSee('Humana');

        // VITAL PF sem obs: 4 combinações; gabarito do PDF pg5 (faixa 00-18)
        $resposta = $this->actingAs($user)->post('/humanas/precos', ['plano_id' => 1])
            ->assertOk()
            ->json();

        $this->assertCount(4, $resposta['tabelas']);
        $enfCompleta = collect($resposta['tabelas'])
            ->firstWhere(fn ($t) => $t['acomodacao'] === 'enfermaria' && $t['coparticipacao'] === 'completa');
        $this->assertSame(156.74, $enfCompleta['precos']['1']['saude']);
        $this->assertSame(143.21, $enfCompleta['precos']['1']['essencial']);
        $this->assertSame(148.58, $enfCompleta['precos']['1']['pleno']);
        $this->assertSame('502.540/25-1', $enfCompleta['registro_ans']);

        // PME usa rótulos Coletiva/Individual
        $pme = $this->actingAs($user)->post('/humanas/precos', ['plano_id' => 7])->assertOk()->json();
        $this->assertContains('Coletiva', array_column($pme['tabelas'], 'acomodacao_label'));

        // REFERÊNCIA: 1 tabela, combos nulos
        $ref = $this->actingAs($user)->post('/humanas/precos', ['plano_id' => 5])->assertOk()->json();
        $this->assertCount(1, $ref['tabelas']);
        $this->assertNull($ref['tabelas'][0]['precos']['1']['essencial']);
    }

    public function test_liberacao_desativada_volta_a_bloquear(): void
    {
        $user = $this->usuarioComAssinatura();
        $this->liberarHumana($user);
        $this->actingAs($user)->get('/humanas')->assertOk();

        $this->liberarHumana($user, ativo: false);
        // instância fresca: o helper memoiza por request, e no teste o mesmo
        // objeto User atravessaria os dois requests carregando o cache antigo
        $this->actingAs($user->fresh())->get('/humanas')->assertForbidden();
    }

    public function test_gerar_rejeita_combinacao_invalida_e_sem_vidas(): void
    {
        $this->prepararCatalogo();
        $user = $this->usuarioComAssinatura();
        $this->liberarHumana($user);

        // REFERÊNCIA não existe com apartamento/completa
        $this->actingAs($user)->post('/humanas/gerar', [
            'plano_id' => 5, 'acomodacao' => 'apartamento', 'coparticipacao' => 'completa',
            'faixas' => [1 => 1], 'tipo_documento' => 'pdf',
        ])->assertNotFound();

        // Nenhuma vida informada
        $this->actingAs($user)->post('/humanas/gerar', [
            'plano_id' => 1, 'acomodacao' => 'enfermaria', 'coparticipacao' => 'completa',
            'faixas' => [1 => 0], 'tipo_documento' => 'pdf',
        ])->assertStatus(422);
    }

    public function test_gerar_pdf_retorna_documento(): void
    {
        $this->prepararCatalogo();
        $user = $this->usuarioComAssinatura();
        $this->liberarHumana($user);

        $resposta = $this->actingAs($user)->post('/humanas/gerar', [
            'plano_id' => 1, 'acomodacao' => 'enfermaria', 'coparticipacao' => 'completa',
            'faixas' => [1 => 1], 'tipo_documento' => 'pdf',
        ]);

        $resposta->assertOk();
        $this->assertStringStartsWith('%PDF', $resposta->getContent());
    }

    public function test_gerar_com_produto_unico_e_comparativo(): void
    {
        $this->prepararCatalogo();
        $user = $this->usuarioComAssinatura();
        $this->liberarHumana($user);

        // Produto único (pedido da usuária): PDF sai só com a coluna escolhida
        $unico = $this->actingAs($user)->post('/humanas/gerar', [
            'plano_id' => 1, 'acomodacao' => 'enfermaria', 'coparticipacao' => 'completa',
            'faixas' => [1 => 1], 'tipo_documento' => 'pdf', 'produto' => 'essencial',
        ]);
        $unico->assertOk();
        $this->assertStringStartsWith('%PDF', $unico->getContent());

        // Comparativo: Completa × Básica no mesmo documento
        $comparativo = $this->actingAs($user)->post('/humanas/gerar', [
            'plano_id' => 1, 'acomodacao' => 'enfermaria', 'coparticipacao' => 'completa',
            'faixas' => [1 => 1], 'tipo_documento' => 'pdf', 'produto' => 'todos', 'comparativo' => 1,
        ]);
        $comparativo->assertOk();
        $this->assertStringStartsWith('%PDF', $comparativo->getContent());

        // REFERÊNCIA não tem as duas copays -> comparativo é 404
        $this->actingAs($user)->post('/humanas/gerar', [
            'plano_id' => 5, 'acomodacao' => 'enfermaria', 'coparticipacao' => 'nao_se_aplica',
            'faixas' => [1 => 1], 'tipo_documento' => 'pdf', 'comparativo' => 1,
        ])->assertNotFound();
    }

    public function test_equipe_da_mesma_assinatura_nao_herda_o_acesso(): void
    {
        $titular = $this->usuarioComAssinatura();
        $this->liberarHumana($titular);

        // Colega na MESMA assinatura do titular, sem liberação própria
        $assinaturaId = EmailAssinatura::where('email', $titular->email)->first()->assinatura_id;
        $colega = User::factory()->create(['layout_id' => null]);
        EmailAssinatura::create([
            'assinatura_id'    => $assinaturaId,
            'email'            => $colega->email,
            'user_id'          => $colega->id,
            'is_administrador' => 0,
        ]);

        $this->actingAs($titular)->get('/humanas')->assertOk();
        $this->actingAs($colega)->get('/humanas')->assertForbidden();
    }

    public function test_importador_e_idempotente(): void
    {
        $this->prepararCatalogo();
        $this->assertSame(40, HumanaTabela::count());
        $this->assertSame(400, HumanaPreco::count());

        Artisan::call('humana:importar', ['arquivo' => 'teresina-2026-08.json']);
        $this->assertSame(40, HumanaTabela::count());
        $this->assertSame(400, HumanaPreco::count());
    }
}
