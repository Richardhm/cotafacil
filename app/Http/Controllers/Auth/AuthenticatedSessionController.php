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
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    private const UUID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    // Espelha o cf_device_uuid do localStorage para sobreviver à limpeza de dados do navegador
    private const DEVICE_COOKIE = 'cf_device_uuid';

    public function create(Request $request)
    {
        // Detecta navegador de celular (define o banner do app e o modo pagamento)
        $ua = $request->userAgent() ?? '';
        $isMobileBrowser = preg_match('/Android|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua)
            && !str_contains($ua, 'iPad');

        // Exceção: regularizar assinatura. O app bloqueia o cliente com assinatura
        // vencida e o manda para cá, mas quem está na rua só tem o celular. Esta
        // sessão entra SEM registrar dispositivo e fica trancada nas rotas de
        // assinatura pelo middleware SomentePagamento — não serve para usar o sistema.
        //
        // Aceita tanto ?pagamento=1 explícito quanto o destino pretendido guardado
        // pelo Laravel: o botão "Regularizar" do app aponta para /assinatura/alterar,
        // e o desvio para o login descarta a query string. Ler url.intended faz o
        // fluxo funcionar com o app que já está publicado, sem novo build.
        $intended = (string) $request->session()->get('url.intended', '');
        $querPagar = $request->boolean('pagamento') || str_contains($intended, '/assinatura');

        $modoPagamento = $isMobileBrowser && $querPagar;

        // App opcional (01/09/2026): o celular abre o login normal pelo navegador,
        // com um banner sugerindo o aplicativo. A view auth/mobile-redirect ficou
        // órfã de propósito — rollback fácil: restaurar aqui o
        // "if ($isMobileBrowser && ! $modoPagamento) return view('auth.mobile-redirect');".
        return response()
            ->view('auth.login', [
                'modoPagamento' => $modoPagamento,
                'sugerirApp'    => $isMobileBrowser && ! $modoPagamento,
            ])
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

        // Valida o UUID enviado pelo localStorage
        $deviceUuid = (string) $request->input('device_uuid', '');
        if (!preg_match(self::UUID_REGEX, $deviceUuid)) {
            $deviceUuid = '';
        }

        // Fallback em cookie: o localStorage é isolado por origem e some quando o usuário
        // limpa os dados do navegador. Se o UUID recebido é desconhecido para esta conta
        // mas o cookie aponta para um dispositivo já cadastrado, reaproveita o cadastro
        // em vez de queimar uma vaga nova no mesmo computador.
        $cookieUuid = (string) $request->cookie(self::DEVICE_COOKIE, '');
        if ($cookieUuid !== $deviceUuid && preg_match(self::UUID_REGEX, $cookieUuid)) {
            $conheceEnviado = $deviceUuid !== '' && UserDevice::where('user_id', $userId)
                ->where('device_uuid', $deviceUuid)
                ->exists();

            $conheceCookie = UserDevice::where('user_id', $userId)
                ->where('device_uuid', $cookieUuid)
                ->exists();

            if (! $conheceEnviado && $conheceCookie) {
                $deviceUuid = $cookieUuid;
            }
        }

        if ($deviceUuid === '') {
            $deviceUuid = (string) Str::uuid();
        }

        $fingerprint = app(DeviceFingerprintService::class)->fromRequest($request);

        // Sessão de pagamento (celular vindo do app para regularizar assinatura):
        // não registra dispositivo e não consome vaga. A sessão é marcada e o
        // middleware SomentePagamento a tranca nas rotas de assinatura.
        if ($request->boolean('pagamento') && $fingerprint['is_mobile']) {
            return $this->iniciarSessaoDePagamento($request, $userId, $ip, $fingerprint);
        }

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

        // App opcional: celular pelo navegador ocupa a MESMA vaga do aplicativo
        // (device_type 'mobile_app' contra max_mobiles). A API de produção só conta
        // linhas mobile_app — um tipo próprio faria 1 aparelho queimar 2 vagas.
        $tipo = $fingerprint['is_mobile'] ? 'mobile_app' : 'desktop';

        // Limite por conta (default 1) — antes era ">= 1" fixo em três pontos,
        // e liberar um computador a mais exigia UPDATE na mão em user_devices.
        $limite = $fingerprint['is_mobile'] ? Auth::user()->limiteMobiles() : Auth::user()->limiteDesktops();

        DB::transaction(function () use ($userId, $deviceUuid, $fingerprint, $tipo, $limite, &$device, &$limitReached) {
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

                // Device inativo — verifica vaga do tipo
                $totalAtivos = UserDevice::where('user_id', $userId)
                    ->where('device_type', $tipo)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->count();

                if ($totalAtivos >= $limite) {
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
                ->where('device_type', $tipo)
                ->where('is_blocked', false)
                ->lockForUpdate()
                ->first();

            if ($byHardware) {
                if (!$byHardware->is_active) {
                    $totalAtivos = UserDevice::where('user_id', $userId)
                        ->where('device_type', $tipo)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->count();

                    if ($totalAtivos >= $limite) {
                        $limitReached = true;
                        return;
                    }

                    $byHardware->update(['is_active' => true]);
                }
                $device = $byHardware->fresh();
                return;
            }

            // Rede de segurança (mesma filosofia do AuthController da API): identidade
            // de navegador mobile é instável (iOS limpa localStorage, cookie some).
            // Com limite 1 e exatamente UMA linha mobile não bloqueada, UUID/hardware
            // desconhecido REAPROVEITA a vaga trocando o UUID — é o dono trocando de
            // identidade, não um segundo aparelho ganhando vaga extra.
            if ($tipo === 'mobile_app' && $limite === 1) {
                $mobiles = UserDevice::where('user_id', $userId)
                    ->where('device_type', 'mobile_app')
                    ->where('is_blocked', false)
                    ->lockForUpdate()
                    ->get();

                if ($mobiles->count() === 1) {
                    $mobiles->first()->update([
                        'device_uuid' => $deviceUuid,
                        'is_active'   => true,
                    ]);
                    $device = $mobiles->first()->fresh();
                    return;
                }
            }

            // Hardware desconhecido — dispositivo verdadeiramente novo
            $totalAtivos = UserDevice::where('user_id', $userId)
                ->where('device_type', $tipo)
                ->where('is_active', true)
                ->lockForUpdate()
                ->count();

            if ($totalAtivos >= $limite) {
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
                'device_type'          => $tipo,
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

            if ($tipo === 'mobile_app') {
                return back()->withErrors([
                    'email' => $limite === 1
                        ? 'Limite de celulares atingido. Sua conta já possui 1 celular registrado. Contate o suporte para trocar de aparelho.'
                        : "Limite de celulares atingido. Sua conta permite {$limite} celulares e todos já estão registrados. Contate o suporte para trocar de aparelho.",
                ])->onlyInput('email');
            }

            return back()->withErrors([
                'email' => $limite === 1
                    ? 'Limite de computadores atingido. Sua conta já possui 1 computador registrado. Contate o suporte para trocar de dispositivo.'
                    : "Limite de computadores atingido. Sua conta permite {$limite} computadores e todos já estão registrados. Contate o suporte para trocar de dispositivo.",
            ])->onlyInput('email');
        }

        // Atualiza fingerprint para refletir versão atual do browser. Em linha
        // mobile NÃO sobrescreve device_name: o app grava ali o modelo do aparelho
        // (ex.: "moto g14") e o AuthController da API reencontra a vaga por esse
        // nome — sobrescrever com "Chrome 138 em Android" quebraria o match.
        $atualizacao = [
            'last_used_at'         => now(),
            'device_fingerprint'   => $fingerprint['device_fingerprint'],
            'hardware_fingerprint' => $fingerprint['hardware_fingerprint'],
            'device_model'         => $fingerprint['device_model'],
        ];

        if ($device->device_type !== 'mobile_app') {
            $atualizacao['device_name'] = trim(($fingerprint['browser'] ?? '') . ' em ' . ($fingerprint['os'] ?? ''));
        }

        $device->update($atualizacao);

        // Persiste o UUID também em cookie (5 anos) — se o localStorage for limpo, o
        // próximo login reencontra este mesmo dispositivo pelo cookie.
        Cookie::queue(self::DEVICE_COOKIE, $deviceUuid, 60 * 24 * 365 * 5);

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

    /**
     * Sessão restrita para regularizar assinatura pelo celular.
     *
     * NÃO registra dispositivo e NÃO consome vaga: o cliente está na rua, com o app
     * bloqueado por assinatura vencida, e precisa pagar. Registrar o celular aqui
     * queimaria a vaga do computador dele (foi o que aconteceu no caso Fabricio,
     * quando o iPhone em "modo desktop" roubou a vaga do desktop).
     *
     * A sessão fica marcada com 'modo_pagamento' e o middleware SomentePagamento
     * só a deixa acessar as rotas de assinatura. Sem esse middleware isto seria um
     * bypass universal do limite de dispositivos.
     */
    private function iniciarSessaoDePagamento(Request $request, int $userId, string $ip, array $fingerprint): RedirectResponse
    {
        $request->session()->regenerate();
        $request->session()->put('modo_pagamento', true);

        $geo = app(GeoIpService::class)->resolve($ip);

        LoginSession::create([
            'user_id'            => $userId,
            'session_id'         => session()->getId(),
            'device_uuid'        => '',
            'ip_address'         => $ip,
            'city'               => $geo['city'],
            'country'            => $geo['country'],
            'timezone'           => $fingerprint['timezone'],
            'screen_resolution'  => $fingerprint['screen_resolution'],
            'user_agent'         => $request->userAgent(),
            'device_fingerprint' => $fingerprint['device_fingerprint'],
            'browser'            => $fingerprint['browser'],
            // Marcador auditável — consultar com LIKE '%(pagamento)'
            'os'                 => trim(($fingerprint['os'] ?? '') . ' (pagamento)'),
            'is_mobile'          => true,
            'device_model'       => $fingerprint['device_model'],
            'logged_in_at'       => now(),
        ]);

        return redirect()->route('assinatura.edit');
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
