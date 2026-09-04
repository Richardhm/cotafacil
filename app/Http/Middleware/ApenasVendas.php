<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Página /vendas: vendedora + desenvolvedores.
 *
 * A lista mora AQUI (e não em User::isVendas) de propósito: o User.php de
 * produção tem edição à mão e é o único arquivo divergente do repo — mexer
 * nele via git geraria conflito no pull.
 */
class ApenasVendas
{
    private const EMAILS_VENDAS = [
        'maryaeduardaaccert@gmail.com', // Marya Eduarda — começou nas vendas em 20/08/2026
    ];

    public static function autorizado(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isDesenvolvedor() || in_array($user->email, self::EMAILS_VENDAS);
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! self::autorizado(auth()->user())) {
            abort(403, 'Acesso não autorizado.');
        }

        return $next($request);
    }
}
