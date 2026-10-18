<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionExpired
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


            // Usa a mesma regra do CheckSubscription (trial, PIX e cartão): se a
            // página de expirada só reconhecesse trial/PIX, a equipe de uma
            // assinatura de cartão vencida entraria em loop de redirect.
            if (! $assinatura->estaVencida()) {
                return redirect()->route('dashboard');
            }


        }

        return $next($request);
    }
}
