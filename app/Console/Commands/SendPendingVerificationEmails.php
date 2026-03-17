<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Jobs\SendVerificationEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPendingVerificationEmails extends Command
{
    protected $signature = 'email:send-pending-verifications';
    protected $description = 'Envia e-mails de verificação para usuários pendentes';

    public function handle()
    {
        // Busca usuários que nunca receberam o e-mail
        $pendingUsers = User::whereNull('email_check_send')->get();

        foreach ($pendingUsers as $user) {
            // Dispara o e-mail de verificação
            SendVerificationEmail::dispatch($user);

            // Atualiza a coluna com o horário de envio
            $user->update(['email_check_send' => now()]);

            $this->info("E-mail enviado para: {$user->email}");
        }

        Log::info('E-mails de verificação enviados para usuários pendentes.');
    }
}
