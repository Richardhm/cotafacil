<?php

namespace App\Notifications;

use App\Models\Assinatura;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PixVencimentoNotification extends Notification
{
    use Queueable;

    public function __construct(private Assinatura $assinatura) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $diasRestantes = (int) now()->diffInDays($this->assinatura->next_charge);
        $dataVencimento = $this->assinatura->next_charge->format('d/m/Y');
        $renewalUrl = route('assinatura.edit');

        return (new MailMessage)
            ->subject("⚠️ Sua assinatura PIX vence em {$diasRestantes} dia(s) - " . config('app.name'))
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Sua assinatura via PIX vencerá em **{$diasRestantes} dia(s)**, no dia **{$dataVencimento}**.")
            ->line('Para continuar usando o sistema sem interrupções, renove seu pagamento antes do vencimento.')
            ->action('Renovar assinatura', $renewalUrl)
            ->line('Após o vencimento, o acesso ao sistema será bloqueado até a confirmação do pagamento.')
            ->salutation('Atenciosamente, Equipe ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
