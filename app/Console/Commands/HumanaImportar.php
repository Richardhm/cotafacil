<?php

namespace App\Console\Commands;

use App\Models\Humana\HumanaFaixaEtaria;
use App\Models\Humana\HumanaPlano;
use App\Models\Humana\HumanaPreco;
use App\Models\Humana\HumanaTabela;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Importa os preços da Humana a partir de um JSON versionado em
 * database/data/humana/ (gerado pela extração dos PDFs oficiais).
 *
 * Idempotente: upsert pela chave natural (plano, acomodação, coparticipação)
 * e (tabela, faixa). Reajuste mensal = novo JSON + rodar de novo.
 * Nunca apaga nada; tabela que sair do PDF fica como está (desativar à mão).
 */
class HumanaImportar extends Command
{
    protected $signature = 'humana:importar {arquivo : caminho do JSON (ou nome dentro de database/data/humana)}
                            {--dry-run : valida e mostra o que faria, sem gravar}';

    protected $description = 'Importa/atualiza precos da operadora Humana a partir de um JSON versionado';

    public function handle(): int
    {
        $caminho = $this->argument('arquivo');
        if (!is_file($caminho)) {
            $caminho = database_path('data/humana/' . $caminho);
        }
        if (!is_file($caminho)) {
            $this->error("Arquivo nao encontrado: {$this->argument('arquivo')}");
            return self::FAILURE;
        }

        $dados = json_decode(file_get_contents($caminho), true);
        if (!is_array($dados) || empty($dados['tabelas'])) {
            $this->error('JSON invalido: chave "tabelas" ausente ou vazia.');
            return self::FAILURE;
        }

        // ---- Validações antes de tocar no banco ----
        $erros    = [];
        $faixaIds = HumanaFaixaEtaria::pluck('id')->all();
        $planoIds = HumanaPlano::pluck('id')->all();

        foreach ($dados['tabelas'] as $i => $t) {
            $rotulo = $t['fonte'] ?? "tabela #$i";
            foreach (['plano_id', 'acomodacao', 'coparticipacao', 'precos'] as $campo) {
                if (!isset($t[$campo])) $erros[] = "$rotulo: falta '$campo'";
            }
            if (isset($t['plano_id']) && !in_array($t['plano_id'], $planoIds)) {
                $erros[] = "$rotulo: plano_id {$t['plano_id']} nao existe em humana_planos";
            }
            $faixas = array_map('intval', array_keys($t['precos'] ?? []));
            sort($faixas);
            if ($faixas !== $faixaIds) {
                $erros[] = "$rotulo: esperava as faixas 1..10, achou [" . implode(',', $faixas) . ']';
            }
            foreach ($t['precos'] ?? [] as $faixa => $v) {
                if (!is_array($v) || count($v) !== 3 || !is_numeric($v[0]) || $v[0] <= 0) {
                    $erros[] = "$rotulo faixa $faixa: valores invalidos";
                }
            }
        }
        if ($erros) {
            foreach ($erros as $e) $this->error($e);
            return self::FAILURE;
        }

        $this->info(sprintf(
            'JSON ok: %d tabelas | vigencia %s a %s | fonte: %s',
            count($dados['tabelas']),
            $dados['vigencia_inicio'] ?? '?',
            $dados['vigencia_fim'] ?? '?',
            basename($caminho)
        ));

        // ---- Gravação (transação; --dry-run dá rollback no final) ----
        $stats = ['tabelas_novas' => 0, 'tabelas_atualizadas' => 0, 'precos_novos' => 0, 'precos_atualizados' => 0, 'precos_iguais' => 0];

        DB::beginTransaction();
        try {
            foreach ($dados['tabelas'] as $t) {
                $tabela = HumanaTabela::firstOrNew([
                    'humana_plano_id' => $t['plano_id'],
                    'acomodacao'      => $t['acomodacao'],
                    'coparticipacao'  => $t['coparticipacao'],
                ]);
                $tabela->fill([
                    'registro_ans'    => $t['registro_ans'] ?? null,
                    'vigencia_inicio' => $dados['vigencia_inicio'] ?? null,
                    'vigencia_fim'    => $dados['vigencia_fim'] ?? null,
                ]);
                $tabela->exists ? $stats['tabelas_atualizadas']++ : $stats['tabelas_novas']++;
                $tabela->save();

                foreach ($t['precos'] as $faixaId => [$saude, $essencial, $pleno]) {
                    $preco = HumanaPreco::firstOrNew([
                        'humana_tabela_id'       => $tabela->id,
                        'humana_faixa_etaria_id' => (int) $faixaId,
                    ]);
                    $preco->fill([
                        'valor_saude'           => $saude,
                        'valor_combo_essencial' => $essencial,
                        'valor_combo_pleno'     => $pleno,
                    ]);
                    if (!$preco->exists) {
                        $stats['precos_novos']++;
                    } elseif ($preco->isDirty()) {
                        $stats['precos_atualizados']++;
                    } else {
                        $stats['precos_iguais']++;
                    }
                    $preco->save();
                }
            }

            if ($this->option('dry-run')) {
                DB::rollBack();
                $this->warn('DRY-RUN: nada gravado (rollback).');
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Falhou, rollback executado: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->table(array_keys($stats), [array_values($stats)]);
        $this->info(sprintf(
            'Totais no banco%s: %d tabelas, %d precos.',
            $this->option('dry-run') ? ' (sem as mudancas do dry-run)' : '',
            HumanaTabela::count(),
            HumanaPreco::count()
        ));

        return self::SUCCESS;
    }
}
