<?php

namespace App\Console\Commands;

use App\Models\Assinatura;
use Efi\EfiPay;
use Illuminate\Console\Command;

class CorrigirValoresCartao extends Command
{
    protected $signature = 'cartao:corrigir-valores {--dry-run : Mostra o que seria alterado sem fazer nenhuma chamada à API}';

    protected $description = 'Atualiza o valor das assinaturas de cartão na Efi Bank para usuários com equipe cadastrada.';

    private EfiPay $efi;

    public function __construct()
    {
        parent::__construct();

        $mode    = config('gerencianet.mode');
        $options = [
            'client_id'     => config("gerencianet.{$mode}.client_id"),
            'client_secret' => config("gerencianet.{$mode}.client_secret"),
            'certificate'   => base_path('certs/' . config("gerencianet.{$mode}.certificate_name")),
            'sandbox'       => config('gerencianet.is_sandbox'),
            'debug'         => false,
        ];

        $this->efi = new EfiPay($options);
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Modo DRY-RUN ativo — nenhuma alteração será feita na Efi Bank.');
        }

        $assinaturas = Assinatura::where('tipo', 'cartao')
            ->where('status', 'ativo')
            ->whereNotNull('subscription_id')
            ->where('emails_extra', '>', 0)
            ->get();

        if ($assinaturas->isEmpty()) {
            $this->info('Nenhuma assinatura de cartão com usuários extras encontrada.');
            return self::SUCCESS;
        }

        $this->info("Encontradas {$assinaturas->count()} assinatura(s) com emails_extra > 0.");
        $this->newLine();

        $atualizadas = 0;
        $erros       = 0;

        foreach ($assinaturas as $assinatura) {
            $valorCorreto    = intval($assinatura->preco_total * 100);
            $valorCorretoFmt = 'R$ ' . number_format($assinatura->preco_total, 2, ',', '.');

            try {
                $resposta       = $this->efi->detailSubscription(['id' => $assinatura->subscription_id]);
                $itemsEfi       = $resposta['data']['plan']['items'] ?? [];
                $valorAtualEfi  = collect($itemsEfi)->sum('value');
                $valorAtualFmt  = 'R$ ' . number_format($valorAtualEfi / 100, 2, ',', '.');

                if ($valorAtualEfi === $valorCorreto) {
                    $this->line("  [OK]    Assinatura {$assinatura->id} (sub: {$assinatura->subscription_id}) — valor já correto: {$valorCorretoFmt}");
                    continue;
                }

                $this->warn("  [FIX]   Assinatura {$assinatura->id} (sub: {$assinatura->subscription_id}) — Efi: {$valorAtualFmt} → correto: {$valorCorretoFmt}");

                if (!$dryRun) {
                    $this->efi->updateSubscription(
                        ['id' => $assinatura->subscription_id],
                        ['items' => [['name' => 'Plano Multiusuário', 'value' => $valorCorreto]]]
                    );
                    $this->info("          Atualizado com sucesso.");
                }

                $atualizadas++;

            } catch (\Exception $e) {
                $this->error("  [ERRO]  Assinatura {$assinatura->id} (sub: {$assinatura->subscription_id}): " . $e->getMessage());
                $erros++;
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->warn("DRY-RUN: {$atualizadas} assinatura(s) seriam corrigidas, {$erros} erro(s).");
        } else {
            $this->info("Concluído: {$atualizadas} assinatura(s) corrigida(s), {$erros} erro(s).");
        }

        return $erros > 0 ? self::FAILURE : self::SUCCESS;
    }
}
