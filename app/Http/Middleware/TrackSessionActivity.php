<?php

namespace App\Http\Middleware;

use App\Models\LoginSession;
use App\Models\UserDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackSessionActivity
{
    // Intervalo mínimo entre atualizações para não bater no banco em todo request
    private const UPDATE_INTERVAL = 300;

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (Auth::check()) {
            $sessionId  = $request->session()->getId();
            $cacheKey   = "last_seen_update_{$sessionId}";
            $lastUpdate = $request->session()->get($cacheKey, 0);

            if ((time() - $lastUpdate) >= self::UPDATE_INTERVAL) {
                $session = LoginSession::where('session_id', $sessionId)
                    ->whereNull('logged_out_at')
                    ->first();

                if ($session) {
                    $session->update(['last_seen_at' => now()]);

                    // Mantém last_used_at do dispositivo sincronizado com a atividade real
                    UserDevice::where('user_id', Auth::id())
                        ->where('device_uuid', $session->device_uuid)
                        ->update(['last_used_at' => now()]);
                }

                $request->session()->put($cacheKey, time());
            }
        }

        return $response;
    }
}
