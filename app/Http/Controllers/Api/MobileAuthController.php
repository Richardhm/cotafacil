<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginSession;
use App\Models\UserDevice;
use App\Services\GeoIpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_uuid' => 'nullable|string|max:36',
            'device_name' => 'nullable|string|max:255',
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        $user = Auth::user();

        // Usuário desativado por compartilhamento
        if ($user->status == 2) {
            Auth::guard('web')->logout();
            return response()->json([
                'success' => false,
                'message' => 'Conta desativada por uso indevido. Entre em contato com o suporte.',
            ], 403);
        }

        $ip         = $request->ip();
        $deviceUuid = $request->input('device_uuid', '');
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $deviceUuid)) {
            $deviceUuid = (string) Str::uuid();
        }

        $deviceName = substr($request->input('device_name') ?: ($request->userAgent() ?? 'App Mobile'), 0, 200);

        // Dispositivo bloqueado
        if (UserDevice::where('user_id', $user->id)->where('device_uuid', $deviceUuid)->where('is_blocked', true)->exists()) {
            Auth::guard('web')->logout();
            return response()->json([
                'success' => false,
                'message' => 'Este dispositivo está bloqueado. Entre em contato com o suporte.',
            ], 403);
        }

        $device       = null;
        $limitReached = false;

        DB::transaction(function () use ($user, $deviceUuid, $deviceName, &$device, &$limitReached) {
            $existing = UserDevice::where('user_id', $user->id)
                ->where('device_uuid', $deviceUuid)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                // Mesmo UUID já registrado (re-login no mesmo celular)
                if (!$existing->is_active) {
                    // Estava inativo — verifica se ainda tem vaga mobile
                    $mobileCount = UserDevice::where('user_id', $user->id)
                        ->where('device_type', 'mobile_app')
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->count();

                    if ($mobileCount >= 1) {
                        $limitReached = true;
                        return;
                    }

                    $existing->update(['is_active' => true]);
                }
                $device = $existing->fresh();
                return;
            }

            // UUID novo — verifica se já existe um mobile_app ativo (outro celular)
            $mobileCount = UserDevice::where('user_id', $user->id)
                ->where('device_type', 'mobile_app')
                ->where('is_active', true)
                ->lockForUpdate()
                ->count();

            if ($mobileCount >= 1) {
                $limitReached = true;
                return;
            }

            $device = UserDevice::create([
                'user_id'            => $user->id,
                'device_uuid'        => $deviceUuid,
                'device_fingerprint' => '',
                'device_name'        => $deviceName,
                'device_model'       => null,
                'device_type'        => 'mobile_app',
                'is_active'          => true,
                'is_blocked'         => false,
                'registered_at'      => now(),
                'last_used_at'       => now(),
            ]);
        });

        if ($limitReached) {
            $geo = app(GeoIpService::class)->resolve($ip);
            LoginSession::create([
                'user_id'      => $user->id,
                'session_id'   => Str::uuid(),
                'device_uuid'  => $deviceUuid,
                'ip_address'   => $ip,
                'city'         => $geo['city'],
                'country'      => $geo['country'],
                'user_agent'   => $request->userAgent(),
                'browser'      => 'App Mobile',
                'os'           => null,
                'is_mobile'    => true,
                'logged_in_at' => now(),
                'logged_out_at'=> now(),
                'was_blocked'  => true,
            ]);

            Auth::guard('web')->logout();
            return response()->json([
                'success' => false,
                'message' => 'Este celular não está autorizado. Apenas o celular cadastrado pode acessar esta conta. Entre em contato com o suporte para troca de aparelho.',
            ], 403);
        }

        // Atualiza last_used_at
        $device->update(['last_used_at' => now(), 'device_name' => $deviceName]);

        // Revoga tokens anteriores deste device e cria novo
        $user->tokens()->where('name', 'mobile_' . $deviceUuid)->delete();
        $token = $user->createToken('mobile_' . $deviceUuid, ['*'], now()->addHours(5));

        $geo = app(GeoIpService::class)->resolve($ip);
        LoginSession::create([
            'user_id'      => $user->id,
            'session_id'   => Str::uuid(),
            'device_uuid'  => $deviceUuid,
            'ip_address'   => $ip,
            'city'         => $geo['city'],
            'country'      => $geo['country'],
            'user_agent'   => $request->userAgent(),
            'browser'      => 'App Mobile',
            'os'           => null,
            'is_mobile'    => true,
            'logged_in_at' => now(),
            'was_blocked'  => false,
        ]);

        return response()->json([
            'success'          => true,
            'token'            => $token->plainTextToken,
            'expires_at'       => now()->addHours(5)->toIso8601String(),
            'configured_hours' => 5,
            'user'             => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
