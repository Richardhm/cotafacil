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

        foreach ($assinaturas as $assinatura) {
            try {
                $resposta   = $this->efi->detailSubscription(['id' => $assinatura->subscription_id]);
                $statusEfi  = $resposta['data']['status'] ?? null;
                $status     = $statusMap[$statusEfi] ?? $statusEfi;

                // Busca a última cobrança paga
                $charges    = $resposta['data']['charges'] ?? [];
                $ultimaPaga = collect($charges)
                    ->where('status', 'paid')
                    ->sortByDesc('paid_at')
                    ->first();

                $updates = ['status' => $status];

                if ($ultimaPaga) {
                    $updates['last_payment'] = Carbon::parse($ultimaPaga['paid_at']);
                    $updates['next_charge']  = Carbon::parse($ultimaPaga['paid_at'])->addMonth();
                }

                $assinatura->update($updates);

                $this->info("Assinatura {$assinatura->id} → status: {$status}" .
                    ($ultimaPaga ? ", próxima cobrança: " . $updates['next_charge']->format('d/m/Y') : ''));

            } catch (\Exception $e) {
                $this->error("Erro na assinatura {$assinatura->id}: " . $e->getMessage());
            }
        }

        $this->info('Sincronização concluída.');
    }
}
