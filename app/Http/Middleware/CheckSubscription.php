<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->assinaturas) {
            $assinatura = $user->assinaturas;
            $isAdmin = ($user->id === $assinatura->user_id);

            // Trial expirado
            if ($assinatura->status === 'trial' && now()->gt($assinatura->trial_ends_at)) {
                if ($isAdmin) {
                    return redirect()->route('assinatura.edit')
                        ->with('error', 'Seu período de teste acabou. Por favor, atualize sua assinatura!');
                }

                return redirect()->route('assinatura.expirada')
                    ->with('error', 'O período trial expirou. Entre em contato com o administrador.');
            }

            // PIX expirado (vencimento não renovado)
            if (in_array($assinatura->tipo, ['PIX', 'PIX_AUTOMATICO']) && now()->gt($assinatura->next_charge)) {
                if ($isAdmin) {
                    return redirect()->route('assinatura.edit')
                        ->with('error', 'Sua assinatura PIX expirou. Por favor, renove-a para continuar utilizando nossos serviços.');
                }

                return redirect()->route('assinatura.expirada')
                    ->with('error', 'Assinatura expirada. Entre em contato com o administrador.');
            }

            // Cartão — bloqueia por status inativo OU next_charge vencido há mais de 5 dias
            $cartaoInadimplente = $assinatura->tipo === 'cartao' && (
                !in_array($assinatura->status, ['ativo', 'trial']) ||
                ($assinatura->next_charge && now()->gt($assinatura->next_charge->addDays(5)))
            );

            if ($cartaoInadimplente) {
                if ($isAdmin) {
                    return redirect()->route('assinatura.edit')
                        ->with('error', 'Sua assinatura está com pagamento pendente. Por favor, atualize seu cartão.');
                }

                return redirect()->route('assinatura.expirada')
                    ->with('error', 'Assinatura suspensa por inadimplência. Entre em contato com o administrador.');
            }
        }

        return $next($request);
    }
}
