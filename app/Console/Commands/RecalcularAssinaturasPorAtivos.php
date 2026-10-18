<?php

namespace App\Console\Commands;

use App\Models\Assinatura;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalcularAssinaturasPorAtivos extends Command
{
    protected $signature = 'assinaturas:recalcular-ativos {--dry-run : Mostra o que mudaria sem gravar nada no banco}';

    protected $description = 'Corrige emails_extra e preco_total de todas as assinaturas pagas pela contagem real de usuários ativos (correção única do drift do ±1 antigo). Não chama a Efi: rodar cartao:corrigir-valores em seguida para espelhar.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Modo DRY-RUN ativo — nenhuma alteração será gravada.');
        }

        // Só pagantes ativas: free fica intocada e trial/promocional tem preço próprio.
        $assinaturas = Assinatura::with(['emails' => fn ($q) => $q->where('is_administrador', true)->with('user')])
            ->where('status', 'ativo')
            ->where(fn ($q) => $q->whereNull('free')->orWhere('free', 0))
            ->orderBy('id')
            ->get();

        if ($assinaturas->isEmpty()) {
            $this->info('Nenhuma assinatura paga ativa encontrada.');
            return self::SUCCESS;
        }

        $this->info("Encontradas {$assinaturas->count()} assinatura(s) paga(s) ativa(s).");
        $this->newLine();

        $corrigidas = 0;

        DB::beginTransaction();

        foreach ($assinaturas as $assinatura) {
            $extraAntes = (int) $assinatura->emails_extra;
            $totalAntes = (float) $assinatura->preco_total;

            $assinatura->recalcularPrecoPorAtivos();

            $extraDepois = (int) $assinatura->emails_extra;
            $totalDepois = (float) $assinatura->preco_total;

            $admin = $assinatura->emails->first()?->user?->email ?? '—';
            $fmt   = fn (float $v) => 'R$ ' . number_format($v, 2, ',', '.');
            $tag   = $assinatura->preco_fixo ? ' [preço combinado]' : '';

            if ($extraAntes === $extraDepois && abs($totalAntes - $totalDepois) < 0.005) {
                $this->line("  [OK]  Assinatura {$assinatura->id} ({$admin}) — já correta: {$extraDepois} ativo(s), {$fmt($totalDepois)}{$tag}");
                continue;
            }

            $this->warn("  [FIX] Assinatura {$assinatura->id} ({$admin}) — ativos {$extraAntes} → {$extraDepois}, {$fmt($totalAntes)} → {$fmt($totalDepois)}{$tag}");
            $corrigidas++;
        }

        $dryRun ? DB::rollBack() : DB::commit();

        $this->newLine();

        if ($dryRun) {
            $this->warn("DRY-RUN: {$corrigidas} assinatura(s) seriam corrigidas (nada foi gravado).");
        } else {
            $this->info("Concluído: {$corrigidas} assinatura(s) corrigida(s).");
            if ($corrigidas > 0) {
                $this->comment('Agora rode "php artisan cartao:corrigir-valores --dry-run" para espelhar os novos valores na Efi.');
            }
        }

        return self::SUCCESS;
    }
}
