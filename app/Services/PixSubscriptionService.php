<?php

namespace App\Services;

use App\Models\Assinatura;
use App\Models\PixPagamento;
use App\Models\PixPendente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Liberação/renovação de assinatura paga via PIX.
 *
 * Fonte única de verdade chamada por DOIS caminhos:
 *  - Webhook da Efi (CallbackController@pixWebhook) → tempo real.
 *  - Polling do navegador (AssinaturaController@verificarPagamentoPIX) → reserva.
 *
 * É idempotente: a existência de um PixPagamento para o txid é a trava definitiva,
 * reforçada por transação + lockForUpdate, então rodar os dois caminhos ao mesmo
 * tempo nunca renova/cobra em dobro.
 */
class PixSubscriptionService
{
    /**
     * Confirma a renovação referente a um txid de PIX já pago na Efi.
     * Retorna a assinatura renovada (ou já renovada), ou null se não aplicável.
     */
    public function confirmarRenovacao(string $txid): ?Assinatura
    {
        return DB::transaction(function () use ($txid) {
            $pendente = PixPendente::where('txid', $txid)->lockForUpdate()->first();

            if (! $pendente || $pendente->tipo !== 'renewal') {
                return null;
            }

            // Já processado antes (idempotência): não renova de novo.
            $jaPago = PixPagamento::where('txid', $txid)->first();
            if ($jaPago) {
                if ($pendente->status !== 'approved') {
                    $pendente->update(['status' => 'approved']);
                }
                return Assinatura::find($jaPago->assinatura_id);
            }

            $assinatura = $pendente->assinatura_id
                ? Assinatura::find($pendente->assinatura_id)
                : optional(User::find($pendente->user_id))->assinaturas()->first();

            if (! $assinatura) {
                return null;
            }

            $assinatura->next_charge   = Carbon::now()->addMonth();
            if ($pendente->valor !== null) {
                $assinatura->preco_total = $pendente->valor;
            }
            $assinatura->tipo          = 'PIX';
            $assinatura->status        = 'ativo';
            $assinatura->trial_ends_at = null;
            $assinatura->save();

            $pendente->update(['status' => 'approved']);

            PixPagamento::create([
                'user_id'       => $pendente->user_id ?? $assinatura->user_id,
                'assinatura_id' => $assinatura->id,
                'txid'          => $txid,
                'valor'         => $pendente->valor ?? $assinatura->preco_total,
                'tipo'          => 'PIX',
                'pago_em'       => Carbon::now(),
            ]);

            return $assinatura;
        });
    }
}
