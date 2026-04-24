<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CheckBlockedIp
{
    private const ROTAS_LIBERADAS = ['login', 'password.request', 'password.email', 'password.reset', 'password.update'];

    private const IPS_PRIVADOS = ['127.0.0.1', '::1'];
    private const PREFIXOS_PRIVADOS = ['192.168.', '10.', '172.16.', '172.17.', '172.18.',
        '172.19.', '172.20.', '172.21.', '172.22.', '172.23.', '172.24.', '172.25.',
        '172.26.', '172.27.', '172.28.', '172.29.', '172.30.', '172.31.'];

    public function handle(Request $request, Closure $next)
    {
        // Nunca bloquear nas páginas públicas — evita loop de redirect
        if ($request->routeIs(...self::ROTAS_LIBERADAS)) {
            return $next($request);
        }

        $ip = $request->ip();

        // IPs privados/loopback nunca são bloqueados (evita travar ambiente de dev)
        if (in_array($ip, self::IPS_PRIVADOS, true)) {
            return $next($request);
        }
        foreach (self::PREFIXOS_PRIVADOS as $prefixo) {
            if (str_starts_with($ip, $prefixo)) {
                return $next($request);
            }
        }

        $bloqueado = Cache::remember("blocked_ip_{$ip}", 120, function () use ($ip) {
            return BlockedIp::where('ip_address', $ip)->exists();
        });

        if ($bloqueado) {
            if (Auth::check()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')
                ->withErrors(['error' => 'Seu acesso foi bloqueado. Entre em contato com o suporte.']);
        }

        return $next($request);
    }
}
