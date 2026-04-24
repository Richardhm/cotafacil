<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoginSession;
use App\Services\DeviceFingerprintService;
use App\Services\GeoIpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        // Solicita ao Chrome/Android que envie o modelo do dispositivo no submit do formulário
        return response()
            ->view('auth.login')
            ->header('Accept-CH', 'Sec-CH-UA-Model');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $userId       = Auth::id();
        $fingerprint  = app(DeviceFingerprintService::class)->fromRequest($request);
        $oldSessionId = session()->getId();

        // Marcar sessões anteriores como deslocadas antes de excluí-las
        $sessoesAnteriores = DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $oldSessionId)
            ->pluck('id');

        if ($sessoesAnteriores->isNotEmpty()) {
            LoginSession::where('user_id', $userId)
                ->whereIn('session_id', $sessoesAnteriores)
                ->whereNull('logged_out_at')
                ->update([
                    'logged_out_at' => now(),
                    'was_displaced'  => true,
                ]);

            DB::table('sessions')
                ->whereIn('id', $sessoesAnteriores)
                ->delete();
        }

        $geo = app(GeoIpService::class)->resolve($request->ip());

        // Regenera ANTES de criar o LoginSession para que o session_id gravado
        // seja o definitivo — o ID muda após regenerate() e queries futuras
        // (TrackSessionActivity, logout) dependem desse ID bater.
        $request->session()->regenerate();

        LoginSession::create([
            'user_id'            => $userId,
            'session_id'         => session()->getId(),
            'ip_address'         => $request->ip(),
            'city'               => $geo['city'],
            'country'            => $geo['country'],
            'timezone'           => $fingerprint['timezone'],
            'screen_resolution'  => $fingerprint['screen_resolution'],
            'canvas_hash'        => $fingerprint['canvas_hash'],
            'gpu_renderer'       => $fingerprint['gpu_renderer'],
            'cpu_cores'          => $fingerprint['cpu_cores'],
            'device_memory'      => $fingerprint['device_memory'],
            'user_agent'         => $request->userAgent(),
            'device_fingerprint' => $fingerprint['device_fingerprint'],
            'browser'            => $fingerprint['browser'],
            'os'                 => $fingerprint['os'],
            'is_mobile'          => $fingerprint['is_mobile'],
            'device_model'       => $fingerprint['device_model'],
            'logged_in_at'       => now(),
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $sessionId = $request->session()->getId();

        LoginSession::where('session_id', $sessionId)
            ->whereNull('logged_out_at')
            ->update(['logged_out_at' => now()]);

        Auth::guard('web')->logout();

        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
