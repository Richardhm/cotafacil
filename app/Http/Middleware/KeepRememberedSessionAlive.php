<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Faz o "Lembrar-me" ter efeito real sem usar o login persistente nativo do Laravel.
 *
 * Regra da sessão:
 *  - Padrão (não marcou): expire_on_close=true → o cookie é de sessão e morre ao fechar
 *    o navegador.
 *  - Marcou "Lembrar-me": o login grava 'remember_until' (agora + 8h) na sessão. Enquanto
 *    esse valor existir, reativamos a persistência do cookie (expire_on_close=false), então
 *    ele sobrevive ao fechar o navegador. O limite de 8h é garantido pelo próprio
 *    remember_until, aplicado no middleware TrackSessionActivity (que desloga ao expirar).
 *
 * Precisa rodar no grupo "web" (dentro do StartSession): assim a mutação de config acontece
 * antes de o StartSession gravar o cookie de sessão na resposta.
 */
class KeepRememberedSessionAlive
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->hasSession() && $request->session()->has('remember_until')) {
            config(['session.expire_on_close' => false]);
        }

        return $response;
    }
}
