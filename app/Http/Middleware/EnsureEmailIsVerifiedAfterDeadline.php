<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureEmailIsVerifiedAfterDeadline
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user(); // Obtém o usuário logado

        if ($user) {
            // Verifica se o e-mail foi enviado e se passou o prazo de 24 horas sem confirmação
            if ($user->email_check_send && !$user->email_verified_at) {
                $hoursSinceSent = now()->diffInHours($user->email_check_send);

                if ($hoursSinceSent > 24) {
                    // Redireciona o usuário para a tela de verificação pendente
                    return redirect()->route('verificacao.pendente');
                }
            }
        }

        return $next($request);
    }
}
