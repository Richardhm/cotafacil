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
            if ($assinatura->trialVencido()) {
                if ($isAdmin) {
                    return redirect()->route('assinatura.edit')
                        ->with('error', 'Seu período de teste acabou. Por favor, atualize sua assinatura!');
                }

                return redirect()->route('assinatura.expirada')
                    ->with('error', 'O período trial expirou. Entre em contato com o administrador.');
            }

            // PIX expirado (vencimento não renovado)
            if ($assinatura->pixVencido()) {
                if ($isAdmin) {
                    return redirect()->route('assinatura.edit')
                        ->with('error', 'Sua assinatura PIX expirou. Por favor, renove-a para continuar utilizando nossos serviços.');
                }

                return redirect()->route('assinatura.expirada')
                    ->with('error', 'Assinatura expirada. Entre em contato com o administrador.');
            }

            // Cartão — bloqueia por status inativo OU vencido além da carência.
            // A regra vive no modelo (Assinatura::cartaoInadimplente), que compara o
            // tipo sem diferenciar maiúsculas: o banco tem 'cartao' E 'CARTAO'.
            if ($assinatura->cartaoInadimplente()) {
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
