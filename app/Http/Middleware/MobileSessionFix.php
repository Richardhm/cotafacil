<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MobileSessionFix
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 'secure' acompanha a conexão real (com trustProxies, isSecure() enxerga o
        // X-Forwarded-Proto do Nginx). Fixar false marcava o cookie de sessão como
        // não-seguro mesmo em HTTPS.
        config([
            'session.path' => '/',
            'session.domain' => null,
            'session.secure' => $request->isSecure(),
            'session.same_site' => 'lax'
        ]);

        return $next($request);
    }
}
