<?php

namespace App\Console\Commands;

use App\Models\Assinatura;
use Carbon\Carbon;
use Efi\EfiPay;
use Illuminate\Console\Command;

class SincronizarAssinaturasCartao extends Command
{
    protected $signature = 'cartao:sincronizar';

    protected $description = 'Consulta a API da Efi Bank e atualiza status das assinaturas de cartão.';

    private $efi;

    public function __construct()
    {
        parent::__construct();

        $mode       = config('gerencianet.mode');
        $options    = [
            'client_id'     => config("gerencianet.{$mode}.client_id"),
            'client_secret' => config("gerencianet.{$mode}.client_secret"),
            'sandbox'       => $mode === 'sandbox',
            'debug'         => false,
        ];

        $this->efi = new EfiPay($options);
    }

    public function handle(): void
    {
        $assinaturas = Assinatura::where('tipo', 'cartao')
            ->whereNotNull('subscription_id')
            ->get();

        if ($assinaturas->isEmpty()) {
            $this->info('Nenhuma assinatura de cartão encontrada.');
            return;
        }

        $this->info("Verificando {$assinaturas->count()} assinatura(s)...");

        $statusMap = [
            'active'    => 'ativo',
            'inactive'  => 'inativo',
            'suspended' => 'inativo',
            'canceled'  => 'cancelado',
        ];

        $notificationUrl = config('gerencianet.notification_url');

        foreach ($assinaturas as $assinatura) {
            try {
                $resposta   = $this->efi->detailSubscription(['id' => $assinatura->subscription_id]);
                $statusEfi  = $resposta['data']['status'] ?? null;
                $status     = $statusMap[$statusEfi] ?? $statusEfi;

                // Assinaturas antigas foram criadas na Efi sem notification_url e por
                // isso nunca disparam webhook (pagamento/inadimplência só chegariam por
                // aqui). Reinscrever é idempotente, então garante em toda sincronização.
                if ($notificationUrl && $status !== 'cancelado') {
                    try {
                        $this->efi->updateSubscriptionMetadata(
                            ['id' => $assinatura->subscription_id],
                            ['notification_url' => $notificationUrl]
                        );
                    } catch (\Exception $e) {
                        $this->warn("Assinatura {$assinatura->id}: nao registrou notification_url ({$e->getMessage()})");
                    }
                }

                // Busca a última cobrança paga. A Efi devolve o histórico em
                // data.history (charge_id/status/created_at) — NÃO existe
                // data.charges nem paid_at (mesma família do bug data.plan.items,
                // descoberto em 30/10 no caso Nilo: o comando nunca tinha
                // conseguido atualizar next_charge de ninguém).
                $historico  = $resposta['data']['history'] ?? [];
                $ultimaPaga = collect($historico)
                    ->where('status', 'paid')
                    ->sortByDesc('created_at')
                    ->first();

                $updates = ['status' => $status];

                if ($ultimaPaga) {
                    $pagoEm = Carbon::parse($ultimaPaga['created_at']);
                    $updates['last_payment'] = $pagoEm;

                    // Nunca RECUA o vencimento: protege ajustes manuais (ex.: um
                    // perdão com data futura) e o inadimplente real (última paga
                    // antiga ⇒ o vencimento atual já está certo e o bloqueio
                    // corre pela carência, não por aqui).
                    $novoVencimento = $pagoEm->copy()->addMonth();
                    if (! $assinatura->next_charge || $novoVencimento->gt($assinatura->next_charge)) {
                        $updates['next_charge'] = $novoVencimento;
                    }
                }

                $assinatura->update($updates);

                $this->info("Assinatura {$assinatura->id} → status: {$status}" .
                    (isset($updates['next_charge']) ? ", próxima cobrança: " . $updates['next_charge']->format('d/m/Y') : ''));

            } catch (\Exception $e) {
                $this->error("Erro na assinatura {$assinatura->id}: " . $e->getMessage());
            }
        }

        $this->info('Sincronização concluída.');
    }
}
