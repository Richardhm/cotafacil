<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Deixa o banco de produção no mesmo estado do banco local depois da faxina de 2026-07-24:
 * remove a administradora Soluction, as 8 cidades de SP, replica os vínculos da assinatura
 * modelo para todas as assinaturas e tira extras e duplicatas.
 *
 * Roda em simulação por padrão. Só grava com --executar.
 * É idempotente: rodar duas vezes não muda nada na segunda.
 */
class PadronizarVinculos extends Command
{
    protected $signature = 'vinculos:padronizar
                            {--executar : Grava de verdade. Sem esta flag é só simulação.}
                            {--pular-exclusoes : Não mexe em Soluction nem nas cidades de SP.}';

    protected $description = 'Padroniza administradora_planos: exclusões, replicação do modelo, extras e duplicatas';

    /** Cidades de SP a excluir: id => nome esperado (confere antes de apagar). */
    private const CIDADES_SP = [
        14 => 'São Paulo',
        55 => 'Jundiaí',
        56 => 'Mogi das Cruzes',
        57 => 'Santos',
        58 => 'Campinas',
        59 => 'São Bernado do Campo',
        60 => 'Sorocaba',
        61 => 'Americana',
    ];

    private const ADMINISTRADORA_EXCLUIR = [5 => 'Soluction'];

    private bool $executar = false;
    private string $backup = '';
    private string $arquivoBackup = '';

    public function handle(): int
    {
        $this->executar = (bool) $this->option('executar');

        $this->info($this->executar
            ? '>> MODO EXECUÇÃO — o banco vai ser alterado.'
            : '>> SIMULAÇÃO — nada será gravado. Use --executar para valer.');
        $this->newLine();

        if (!$this->validarPreCondicoes()) {
            return self::FAILURE;
        }

        $this->arquivoBackup = 'backup-padronizar-vinculos-' . now()->format('Ymd-His') . '.sql';
        $this->backup = "-- Linhas removidas por vinculos:padronizar em " . now()->toDateTimeString() . "\n"
            . "-- Rodar este arquivo restaura tudo com os IDs originais.\n\n";

        DB::beginTransaction();

        try {
            if ($this->option('pular-exclusoes')) {
                $this->warn('Etapas 1 e 2 puladas (--pular-exclusoes).');
            } else {
                $this->excluirAdministradoras();
                $this->excluirCidades();
            }

            $this->replicarModelo();
            $this->removerExtras();
            $this->removerDuplicadas();

            if ($this->executar) {
                DB::commit();
                file_put_contents(storage_path('app/' . $this->arquivoBackup), $this->backup);
                $this->newLine();
                $this->info('Backup das linhas removidas: storage/app/' . $this->arquivoBackup);
            } else {
                DB::rollBack();
                $this->newLine();
                $this->warn('Simulação encerrada — nada foi gravado.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Falhou, nada foi gravado: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->conferir();

        return self::SUCCESS;
    }

    /**
     * Nada é apagado sem antes conferir que o ID em produção é mesmo o registro esperado —
     * IDs podem divergir entre bancos.
     */
    private function validarPreCondicoes(): bool
    {
        $ok = true;

        $modeloId = (int) config('vinculos.assinatura_modelo');
        $modelo = DB::table('assinaturas')->where('id', $modeloId)->first();

        if (!$modelo) {
            $this->error("Assinatura modelo {$modeloId} não existe. Ajuste VINCULOS_ASSINATURA_MODELO no .env.");

            return false;
        }

        $dono = DB::table('users')->where('id', $modelo->user_id)->first();
        $totalModelo = DB::table('administradora_planos')->where('assinatura_id', $modeloId)->count();

        $this->line("Assinatura modelo: {$modeloId} — " . ($dono->name ?? '?') . ' <' . ($dono->email ?? '?') . ">");
        $this->line("Vínculos no modelo: {$totalModelo}");
        $this->line('Planos excluídos da cópia: ' . implode(', ', (array) config('vinculos.planos_excluidos', [])));
        $this->newLine();

        if ($totalModelo === 0) {
            $this->error('A assinatura modelo não tem nenhum vínculo. Abortando.');

            return false;
        }

        if (!$this->option('pular-exclusoes')) {
            foreach (self::ADMINISTRADORA_EXCLUIR as $id => $nome) {
                $atual = DB::table('administradoras')->where('id', $id)->value('nome');

                if ($atual !== null && $atual !== $nome) {
                    $this->error("Administradora {$id} se chama '{$atual}', esperado '{$nome}'. Abortando.");
                    $ok = false;
                }
            }

            foreach (self::CIDADES_SP as $id => $nome) {
                $atual = DB::table('tabela_origens')->where('id', $id)->value('nome');

                if ($atual !== null && $atual !== $nome) {
                    $this->error("Cidade {$id} se chama '{$atual}', esperado '{$nome}'. Abortando.");
                    $ok = false;
                }
            }
        }

        return $ok;
    }

    private function excluirAdministradoras(): void
    {
        $this->comment('1) Administradoras a remover');

        foreach (self::ADMINISTRADORA_EXCLUIR as $id => $nome) {
            if (!DB::table('administradoras')->where('id', $id)->exists()) {
                $this->line("   {$nome} ({$id}): já removida.");
                continue;
            }

            $removidas = $this->apagarReferencias('administradoras', [$id]);
            $this->guardar('administradoras', DB::table('administradoras')->where('id', $id)->get());

            if ($this->executar) {
                DB::table('administradoras')->where('id', $id)->delete();
            }

            $this->line("   {$nome} ({$id}): " . ($removidas ?: 'nenhuma linha dependente') . ' + a própria administradora');
        }
    }

    private function excluirCidades(): void
    {
        $this->comment('2) Cidades de SP a remover');

        $existentes = DB::table('tabela_origens')->whereIn('id', array_keys(self::CIDADES_SP))->pluck('id')->all();

        if (!$existentes) {
            $this->line('   Todas já removidas.');

            return;
        }

        $removidas = $this->apagarReferencias('tabela_origens', $existentes);
        $this->guardar('tabela_origens', DB::table('tabela_origens')->whereIn('id', $existentes)->get());

        if ($this->executar) {
            DB::table('tabela_origens')->whereIn('id', $existentes)->delete();
        }

        $this->line('   Cidades: ' . count($existentes) . ' | dependentes: ' . ($removidas ?: 'nenhuma'));
    }

    /**
     * Apaga as linhas que apontam para os IDs informados, em todas as tabelas que têm FK
     * para a tabela pai. Descobre por information_schema porque o nome da coluna varia
     * (tabela_origens_id, tabela_origem_id...) e filtrar por nome já deu falso negativo.
     */
    private function apagarReferencias(string $tabelaPai, array $ids): string
    {
        $fks = DB::select('
            SELECT k.TABLE_NAME, k.COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE k
            WHERE k.TABLE_SCHEMA = DATABASE()
              AND k.REFERENCED_TABLE_NAME = ?
        ', [$tabelaPai]);

        $resumo = [];

        foreach ($fks as $fk) {
            $linhas = DB::table($fk->TABLE_NAME)->whereIn($fk->COLUMN_NAME, $ids)->get();

            if ($linhas->isEmpty()) {
                continue;
            }

            $this->guardar($fk->TABLE_NAME, $linhas);

            if ($this->executar) {
                DB::table($fk->TABLE_NAME)->whereIn($fk->COLUMN_NAME, $ids)->delete();
            }

            $resumo[] = $fk->TABLE_NAME . ' ' . $linhas->count();
        }

        return implode(', ', $resumo);
    }

    private function replicarModelo(): void
    {
        $this->comment('3) Replicar o modelo para todas as assinaturas');

        $modelo = $this->combinacoesDoModelo();
        $this->line('   Modelo: ' . count($modelo) . ' combinações');

        $agora = now();
        $inseridas = 0;
        $assinaturasTocadas = 0;

        foreach (DB::table('assinaturas')->orderBy('id')->pluck('id') as $assinaturaId) {
            $existentes = DB::table('administradora_planos')
                ->where('assinatura_id', $assinaturaId)
                ->get()
                ->mapWithKeys(fn ($v) => [$this->chave($v) => true]);

            $novos = [];

            foreach ($modelo as $chave => $combo) {
                if (isset($existentes[$chave])) {
                    continue;
                }

                $novos[] = $combo + [
                    'assinatura_id' => $assinaturaId,
                    'created_at'    => $agora,
                    'updated_at'    => $agora,
                ];
            }

            if (!$novos) {
                continue;
            }

            $assinaturasTocadas++;
            $inseridas += count($novos);

            if ($this->executar) {
                foreach (array_chunk($novos, 500) as $lote) {
                    DB::table('administradora_planos')->insertOrIgnore($lote);
                }
            }
        }

        $this->line("   Linhas a inserir: {$inseridas} em {$assinaturasTocadas} assinatura(s)");
    }

    private function removerExtras(): void
    {
        $this->comment('4) Remover vínculos fora do modelo');

        $modelo = $this->combinacoesDoModelo();

        $extras = DB::table('administradora_planos')
            ->whereNotNull('assinatura_id')
            ->get()
            ->filter(fn ($v) => !isset($modelo[$this->chave($v)]));

        if ($extras->isEmpty()) {
            $this->line('   Nenhum.');

            return;
        }

        $this->guardar('administradora_planos', $extras);

        if ($this->executar) {
            foreach ($extras->pluck('id')->chunk(500) as $lote) {
                DB::table('administradora_planos')->whereIn('id', $lote->all())->delete();
            }
        }

        $this->line('   Linhas: ' . $extras->count() . ' em ' . $extras->pluck('assinatura_id')->unique()->count() . ' assinatura(s)');
    }

    private function removerDuplicadas(): void
    {
        $this->comment('5) Remover duplicatas (mantém o menor id de cada grupo)');

        $duplicadas = DB::table('administradora_planos')
            ->whereNotNull('assinatura_id')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($v) => $v->assinatura_id . '|' . $this->chave($v))
            ->flatMap(fn ($grupo) => $grupo->slice(1))
            ->values();

        if ($duplicadas->isEmpty()) {
            $this->line('   Nenhuma.');

            return;
        }

        $this->guardar('administradora_planos', $duplicadas);

        if ($this->executar) {
            foreach ($duplicadas->pluck('id')->chunk(500) as $lote) {
                DB::table('administradora_planos')->whereIn('id', $lote->all())->delete();
            }
        }

        $this->line('   Linhas: ' . $duplicadas->count());
    }

    private function conferir(): void
    {
        $this->comment('Conferência');

        $modelo = $this->combinacoesDoModelo();
        $divergentes = 0;

        foreach (DB::table('assinaturas')->pluck('id') as $id) {
            $chaves = DB::table('administradora_planos')
                ->where('assinatura_id', $id)
                ->get()
                ->map(fn ($v) => $this->chave($v))
                ->unique()
                ->all();

            if (array_diff(array_keys($modelo), $chaves) || array_diff($chaves, array_keys($modelo))) {
                $divergentes++;
            }
        }

        $this->table(['Item', 'Valor'], [
            ['Assinaturas', DB::table('assinaturas')->count()],
            ['Combinações do modelo', count($modelo)],
            ['Assinaturas divergentes', $divergentes],
            ['administradora_planos (total)', DB::table('administradora_planos')->count()],
            ['  com assinatura', DB::table('administradora_planos')->whereNotNull('assinatura_id')->count()],
            ['  sem assinatura (resíduo antigo)', DB::table('administradora_planos')->whereNull('assinatura_id')->count()],
        ]);

        if ($divergentes > 0 && $this->executar) {
            $this->error("{$divergentes} assinatura(s) ainda divergem do modelo.");
        }
    }

    /** @return array<string, array{administradora_id:int, plano_id:int, tabela_origens_id:int}> */
    private function combinacoesDoModelo(): array
    {
        $excluidos = (array) config('vinculos.planos_excluidos', []);

        $linhas = DB::table('administradora_planos')
            ->where('assinatura_id', (int) config('vinculos.assinatura_modelo'))
            ->whereNotNull('tabela_origens_id')
            ->when($excluidos, fn ($q) => $q->whereNotIn('plano_id', $excluidos))
            ->get();

        $modelo = [];

        foreach ($linhas as $linha) {
            $modelo[$this->chave($linha)] = [
                'administradora_id' => $linha->administradora_id,
                'plano_id'          => $linha->plano_id,
                'tabela_origens_id' => $linha->tabela_origens_id,
            ];
        }

        return $modelo;
    }

    private function chave($linha): string
    {
        return $linha->administradora_id . '-' . $linha->plano_id . '-' . $linha->tabela_origens_id;
    }

    private function guardar(string $tabela, $linhas): void
    {
        if ($linhas->isEmpty()) {
            return;
        }

        $colunas = array_keys((array) $linhas->first());
        $this->backup .= "-- {$tabela}: " . $linhas->count() . " linha(s)\n";

        foreach ($linhas as $linha) {
            $valores = array_map(function ($valor) {
                if ($valor === null) {
                    return 'NULL';
                }

                return is_numeric($valor) ? $valor : "'" . addslashes($valor) . "'";
            }, array_values((array) $linha));

            $this->backup .= 'INSERT INTO ' . $tabela . ' (' . implode(', ', $colunas) . ') VALUES ('
                . implode(', ', $valores) . ");\n";
        }

        $this->backup .= "\n";
    }
}
