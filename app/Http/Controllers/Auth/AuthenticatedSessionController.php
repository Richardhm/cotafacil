<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\BlockedIp;
use App\Models\BlockedUserIp;
use App\Models\LoginSession;
use App\Models\UserDevice;
use App\Services\DeviceFingerprintService;
use App\Services\GeoIpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request)
    {
        // Mobile browsers devem usar o aplicativo
        $ua = $request->userAgent() ?? '';
        $isMobileBrowser = preg_match('/Android|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua)
            && !str_contains($ua, 'iPad');

        if ($isMobileBrowser) {
            return view('auth.mobile-redirect');
        }

        return response()
            ->view('auth.login')
            ->header('Accept-CH', 'Sec-CH-UA-Model, Sec-CH-UA-Platform, Sec-CH-UA-Mobile');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $userId = Auth::id();
        $ip     = $request->ip();

        // Bloquear IP global
        if (BlockedIp::where('ip_address', $ip)->exists()) {
            Auth::guard('web')->logout();
            return back()->withErrors(['email' => 'Acesso bloqueado. Entre em contato com o suporte.'])->onlyInput('email');
        }

        // Bloquear usuário neste IP
        if (BlockedUserIp::where('user_id', $userId)->where('ip_address', $ip)->exists()) {
            Auth::guard('web')->logout();
            return back()->withErrors(['email' => 'Este dispositivo está bloqueado para a sua conta. Entre em contato com o suporte.'])->onlyInput('email');
        }

        // Valida ou gera UUID do dispositivo
        $deviceUuid = $request->input('device_uuid', '');
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $deviceUuid)) {
            $deviceUuid = (string) Str::uuid();
        }

        $fingerprint = app(DeviceFingerprintService::class)->fromRequest($request);

        // Dispositivo explicitamente bloqueado pelo administrador
        if (UserDevice::where('user_id', $userId)->where('device_uuid', $deviceUuid)->where('is_blocked', true)->exists()) {
            Auth::guard('web')->logout();
            return back()->withErrors([
                'email' => 'Este dispositivo está bloqueado. Entre em contato com o suporte.',
            ])->onlyInput('email');
        }

        // Verifica/registra dispositivo com proteção contra race condition
        $device       = null;
        $limitReached = false;

        DB::transaction(function () use ($userId, $deviceUuid, $fingerprint, &$device, &$limitReached) {
            // Busca qualquer registro existente para este UUID (ativo ou inativo)
            $existing = UserDevice::where('user_id', $userId)
                ->where('device_uuid', $deviceUuid)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->is_active) {
                    // Device já ativo — usa normalmente
                    $device = $existing;
                    return;
                }

                // Device inativo — verifica vaga desktop
                $desktopTotal = UserDevice::where('user_id', $userId)
                    ->where('device_type', 'desktop')
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->count();

                if ($desktopTotal >= 1) {
                    $limitReached = true;
                    return;
                }

                $existing->update(['is_active' => true]);
                $device = $existing->fresh();
                return;
            }

            // UUID desconhecido — verifica se o hardware já está cadastrado (outro browser ou aba anônima)
            $byHardware = UserDevice::where('user_id', $userId)
                ->where('hardware_fingerprint', $fingerprint['hardware_fingerprint'])
                ->where('device_type', 'desktop')
                ->where('is_blocked', false)
                ->lockForUpdate()
                ->first();

            if ($byHardware) {
                if (!$byHardware->is_active) {
                    $desktopTotal = UserDevice::where('user_id', $userId)
                        ->where('device_type', 'desktop')
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->count();

                    if ($desktopTotal >= 1) {
                        $limitReached = true;
                        return;
                    }

                    $byHardware->update(['is_active' => true]);
                }
                $device = $byHardware->fresh();
                return;
            }

            // Hardware desconhecido — desktop verdadeiramente novo
            $desktopTotal = UserDevice::where('user_id', $userId)
                ->where('device_type', 'desktop')
                ->where('is_active', true)
                ->lockForUpdate()
                ->count();

            if ($desktopTotal >= 1) {
                $limitReached = true;
                return;
            }

            $device = UserDevice::create([
                'user_id'              => $userId,
                'device_uuid'          => $deviceUuid,
                'device_fingerprint'   => $fingerprint['device_fingerprint'],
                'hardware_fingerprint' => $fingerprint['hardware_fingerprint'],
                'device_name'          => trim(($fingerprint['browser'] ?? '') . ' em ' . ($fingerprint['os'] ?? '')),
                'device_model'         => $fingerprint['device_model'],
                'device_type'          => 'desktop',
                'is_active'            => true,
                'is_blocked'           => false,
                'registered_at'        => now(),
            ]);
        });

        if ($limitReached) {
            // Registra tentativa bloqueada para auditoria
            $geo = app(GeoIpService::class)->resolve($ip);
            $request->session()->regenerate();
            LoginSession::create([
                'user_id'            => $userId,
                'session_id'         => session()->getId(),
                'device_uuid'        => $deviceUuid,
                'ip_address'         => $ip,
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
                'logged_out_at'      => now(),
                'was_blocked'        => true,
            ]);

            Auth::guard('web')->logout();
            return back()->withErrors([
                'email' => 'Limite de computadores atingido. Sua conta já possui 1 computador registrado. Contate o suporte para trocar de dispositivo.',
            ])->onlyInput('email');
        }

        // Atualiza device_name/fingerprint para refletir versão atual do browser
        $device->update([
            'last_used_at'        => now(),
            'device_name'         => trim(($fingerprint['browser'] ?? '') . ' em ' . ($fingerprint['os'] ?? '')),
            'device_fingerprint'  => $fingerprint['device_fingerprint'],
            'hardware_fingerprint' => $fingerprint['hardware_fingerprint'],
            'device_model'        => $fingerprint['device_model'],
        ]);

        // Desloca sessões anteriores do mesmo dispositivo (outros dispositivos ficam ativos)
        $sessoesDoMesmoDispositivo = LoginSession::where('user_id', $userId)
            ->where('device_uuid', $deviceUuid)
            ->whereNull('logged_out_at')
            ->pluck('session_id');

        if ($sessoesDoMesmoDispositivo->isNotEmpty()) {
            LoginSession::where('user_id', $userId)
                ->where('device_uuid', $deviceUuid)
                ->whereNull('logged_out_at')
                ->update(['logged_out_at' => now(), 'was_displaced' => true]);

            DB::table('sessions')->whereIn('id', $sessoesDoMesmoDispositivo)->delete();
        }

        $geo = app(GeoIpService::class)->resolve($request->ip());

        // Regenera ANTES de criar o LoginSession para que o session_id gravado seja o definitivo
        $request->session()->regenerate();

        LoginSession::create([
            'user_id'            => $userId,
            'session_id'         => session()->getId(),
            'device_uuid'        => $deviceUuid,
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

        if ($request->boolean('remember_me')) {
            session(['remember_until' => now()->addHours(8)->timestamp]);
        }

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
