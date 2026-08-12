<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tranca a sessão de pagamento nas rotas de assinatura.
 *
 * O login com ?pagamento=1 (celular vindo do app com assinatura vencida) entra SEM
 * registrar dispositivo e SEM consumir vaga. Sem este middleware, isso seria um
 * bypass universal do limite de dispositivos: bastaria acrescentar ?pagamento=1 na
 * URL para usar o sistema inteiro pelo celular sem gastar vaga nenhuma.
 *
 * A sessão marcada só alcança o que é necessário para pagar. Qualquer outra rota
 * volta para a tela de assinatura com um aviso.
 */
class SomentePagamento
{
    /**
     * Rotas liberadas para a sessão de pagamento (por nome).
     */
    private const ROTAS_LIBERADAS = [
        'assinatura.edit',
        'assinatura.pix',
        'assinatura.pix.trial',
        'assinatura.pix.automatico',
        'assinaturas.trial.store',
        'assinatura.historico',
        'assinatura.historico.pix',
        'assinatura.expirada',
        'logout',
    ];

    /**
     * Prefixos liberados, para os endpoints auxiliares do fluxo de pagamento
     * (verificação de PIX por polling, retorno de callback, etc.).
     */
    private const PREFIXOS_LIBERADOS = [
        'assinatura/',
        'assinaturas/',
        'callback',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('modo_pagamento')) {
            return $next($request);
        }

        $nome = $request->route()?->getName();

        if ($nome && in_array($nome, self::ROTAS_LIBERADAS, true)) {
            return $next($request);
        }

        foreach (self::PREFIXOS_LIBERADOS as $prefixo) {
            if ($request->is($prefixo . '*')) {
                return $next($request);
            }
        }

        // Sessão de pagamento tentando alcançar o resto do sistema.
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Esta sessão serve apenas para regularizar a assinatura.',
            ], 403);
        }

        // Evita laço de redirecionamento caso já esteja indo para a tela de assinatura
        if ($request->routeIs('assinatura.edit')) {
            Auth::guard('web')->logout();
            $request->session()->flush();
            return redirect()->route('login');
        }

        return redirect()->route('assinatura.edit')->with(
            'error',
            'Esta sessão foi aberta apenas para regularizar a assinatura. '
            . 'Depois de pagar, use o aplicativo no celular ou acesse pelo computador.'
        );
    }
}
