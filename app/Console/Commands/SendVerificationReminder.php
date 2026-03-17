<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Jobs\SendVerificationEmail;

class SendVerificationReminder extends Command
{
    /**
     * O nome e a assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'email:send-reminder';

    /**
     * Description para o comando.
     *
     * @var string
     */
    protected $description = 'Envia lembretes de confirmação de e-mail para usuários que ainda não confirmaram e foram cadastrados há mais de 24 horas.';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        // Seleciona usuários pendentes de confirmação
        $usuariosPendentes = User::whereNull('email_verified_at')
            ->where('created_at', '<=', now()->subHours(24))
            ->take(20) // Limitar a 20 por execução
            ->get();

        // Dispara e-mails para cada usuário
        foreach ($usuariosPendentes as $usuario) {
            SendVerificationEmail::dispatch($usuario)
                ->onQueue('emails'); // Opcional: Define uma fila específica para e-mails
            $this->info("E-mail de lembrete enviado para: {$usuario->email}");
        }

        if ($usuariosPendentes->isEmpty()) {
            $this->info("Nenhum e-mail pendente para envio.");
        }
    }
}
