<?php

namespace Tests\Feature;

use App\Models\Assinatura;
use App\Models\EmailAssinatura;
use App\Models\Plano;
use App\Models\RotuloCotacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Rótulos personalizados da cotação Hapvida: aba em /configuracoes (só dev)
 * e cascata usuário > assinatura > padrão.
 */
class RotulosCotacaoTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $email, ?int $assinaturaId = null): array
    {
        $user = User::factory()->create(['email' => $email, 'layout_id' => null]);
        if ($assinaturaId === null) {
            $tipo = DB::table('tipos_planos')->insertGetId(['nome' => 'T', 'valor_base' => 1, 'created_at' => now(), 'updated_at' => now()]);
            $assinaturaId = Assinatura::create([
                'user_id' => $user->id, 'tipo_plano_id' => $tipo, 'preco_base' => 1, 'preco_total' => 1,
                'status' => 'trial', 'trial_ends_at' => now()->addDays(7),
            ])->id;
        }
        EmailAssinatura::create(['assinatura_id' => $assinaturaId, 'email' => $email, 'user_id' => $user->id, 'is_administrador' => 0]);

        return [$user, $assinaturaId];
    }

    public function test_aba_rotulos_aparece_em_configuracoes_para_o_desenvolvedor(): void
    {
        [$dev] = $this->usuario('richardjonhshm@gmail.com');   // e-mail da lista isDesenvolvedor()

        $this->actingAs($dev)->get('/configuracoes')
            ->assertOk()
            ->assertSee('Novo rótulo')
            ->assertSee('#tab13', false);
    }

    public function test_cadastro_edicao_remocao_e_cascata(): void
    {
        [$dev]                  = $this->usuario('richardjonhshm@gmail.com');
        [$titular, $assinatura] = $this->usuario('titular@teste.com');
        [$vendedor]             = $this->usuario('vendedor@teste.com', $assinatura);   // mesma assinatura
        $plano = Plano::create(['nome' => 'Coletivo por Adesão']);

        // 1) rótulo da ASSINATURA: vale para titular e vendedor
        $this->actingAs($dev)->post('/configuracoes/rotulos', [
            'nivel' => 'assinatura', 'assinatura_id' => $assinatura,
            'chave' => 'com_copart', 'texto' => 'coparticipação total',
        ])->assertRedirect();
        $this->assertSame('COPARTICIPAÇÃO TOTAL', RotuloCotacao::resolver($titular, 'com_copart', null, 'X'));
        $this->assertSame('COPARTICIPAÇÃO TOTAL', RotuloCotacao::resolver($vendedor, 'com_copart', null, 'X'));

        // 2) rótulo do USUÁRIO vence o da assinatura — só para ele
        $this->actingAs($dev)->post('/configuracoes/rotulos', [
            'nivel' => 'usuario', 'user_id' => $vendedor->id,
            'chave' => 'com_copart', 'texto' => 'Só Copart',
        ])->assertRedirect();
        $this->assertSame('SÓ COPART', RotuloCotacao::resolver($vendedor, 'com_copart', null, 'X'));
        $this->assertSame('COPARTICIPAÇÃO TOTAL', RotuloCotacao::resolver($titular, 'com_copart', null, 'X'));

        // 3) nome do plano é por plano
        $this->actingAs($dev)->post('/configuracoes/rotulos', [
            'nivel' => 'usuario', 'user_id' => $vendedor->id,
            'chave' => 'nome_plano', 'plano_id' => $plano->id, 'texto' => 'Adesão',
        ])->assertRedirect();
        $this->assertSame('ADESÃO', RotuloCotacao::resolver($vendedor, 'nome_plano', $plano->id, 'Coletivo por Adesão'));
        $this->assertSame('Individual', RotuloCotacao::resolver($vendedor, 'nome_plano', 999, 'Individual'));

        // 4) cadastrar de novo o mesmo alvo SUBSTITUI (não duplica)
        $this->actingAs($dev)->post('/configuracoes/rotulos', [
            'nivel' => 'usuario', 'user_id' => $vendedor->id,
            'chave' => 'com_copart', 'texto' => 'Copart Completa',
        ]);
        $this->assertSame(1, RotuloCotacao::where('user_id', $vendedor->id)->where('chave', 'com_copart')->count());
        $this->assertSame('COPART COMPLETA', RotuloCotacao::resolver($vendedor, 'com_copart', null, 'X'));

        // 5) editar texto e remover (volta ao nível de baixo)
        $rotulo = RotuloCotacao::where('user_id', $vendedor->id)->where('chave', 'com_copart')->first();
        $this->actingAs($dev)->post("/configuracoes/rotulos/{$rotulo->id}/texto", ['texto' => 'editado'])->assertRedirect();
        $this->assertSame('EDITADO', $rotulo->fresh()->texto);
        $this->actingAs($dev)->delete("/configuracoes/rotulos/{$rotulo->id}")->assertRedirect();
        $this->assertSame('COPARTICIPAÇÃO TOTAL', RotuloCotacao::resolver($vendedor, 'com_copart', null, 'X'));

        // 6) usuário comum não acessa a gestão
        $this->actingAs($vendedor)->post('/configuracoes/rotulos', [
            'nivel' => 'usuario', 'user_id' => $vendedor->id, 'chave' => 'com_copart', 'texto' => 'hack',
        ])->assertForbidden();
    }
}
