<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Porteiro do módulo Humana (/humanas): só passa quem tem a assinatura
 * liberada em humana_acessos (ou é desenvolvedor). Aplicar em TODAS as
 * rotas do módulo, inclusive as de AJAX.
 */
class AcessoHumana
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->temAcessoHumana()) {
            abort(403, 'Acesso não autorizado.');
        }

        return $next($request);
    }
}
