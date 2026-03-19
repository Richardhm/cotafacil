<?php

namespace App\Console\Commands;

use App\Models\Assinatura;
use App\Models\EmailAssinatura;
use App\Notifications\PixVencimentoNotification;
use Illuminate\Console\Command;

class EnviarAlertaPixVencimento extends Command
{
    protected $signature = 'pix:alerta-vencimento';

    protected $description = 'Envia alerta por e-mail ao administrador da conta 3 dias antes do vencimento da assinatura PIX.';

    public function handle(): void
    {
        // Busca assinaturas PIX ativas que vencem nos próximos 3 dias
        // e que ainda não receberam o alerta neste ciclo
        $assinaturas = Assinatura::whereIn('tipo', ['PIX', 'PIX_AUTOMATICO'])
            ->where('status', 'ativo')
            ->whereBetween('next_charge', [now(), now()->addDays(3)])
            ->where(function ($query) {
                $query->whereNull('alerta_pix_enviado_at')
                      ->orWhere('alerta_pix_enviado_at', '<', now()->subDays(25));
            })
            ->get();

        if ($assinaturas->isEmpty()) {
            $this->info('Nenhuma assinatura PIX próxima do vencimento.');
            return;
        }

        foreach ($assinaturas as $assinatura) {
            // Busca o administrador da conta
            $emailAdmin = EmailAssinatura::where('assinatura_id', $assinatura->id)
                ->where('is_administrador', true)
                ->with('user')
                ->first();

            if (!$emailAdmin || !$emailAdmin->user) {
                $this->warn("Administrador não encontrado para assinatura ID {$assinatura->id}");
                continue;
            }

            $admin = $emailAdmin->user;

            try {
                $admin->notify(new PixVencimentoNotification($assinatura));

                // Marca o alerta como enviado para não reenviar no mesmo ciclo
                $assinatura->update(['alerta_pix_enviado_at' => now()]);

                $this->info("Alerta enviado para: {$admin->email} (assinatura ID {$assinatura->id}, vence em {$assinatura->next_charge->format('d/m/Y')})");
            } catch (\Exception $e) {
                $this->error("Erro ao enviar para {$admin->email}: {$e->getMessage()}");
            }
        }

        $this->info("Processamento concluído. {$assinaturas->count()} assinatura(s) verificada(s).");
    }
}
