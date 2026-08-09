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
use Illuminate\Support\Facades\Log;
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

        // trim() aqui e não só dentro de nomeIdentificaAparelho(): o nome cru é usado
        // como chave de busca e é gravado no banco, então espaço em volta faria o nome
        // passar na validação e mesmo assim não casar com a linha já existente.
        $deviceName = trim(substr($request->input('device_name') ?: ($request->userAgent() ?? 'App Mobile'), 0, 200));

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
        $uuidTrocado  = false;
        $motivoTroca  = null;

        DB::transaction(function () use ($user, $deviceUuid, $deviceName, &$device, &$limitReached, &$uuidTrocado, &$motivoTroca) {
            $existing = UserDevice::where('user_id', $user->id)
                ->where('device_uuid', $deviceUuid)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                // Mesmo UUID já registrado (re-login no mesmo celular)
                if (!$existing->is_active) {
                    // O storage pode ter voltado a um UUID antigo — libera as vagas
                    // ocupadas por outras linhas do MESMO aparelho antes de contar
                    $this->liberarVagasDoMesmoAparelho($user->id, $deviceName, $existing->id);

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

            // UUID novo — pode ser o MESMO aparelho que perdeu o device_uuid guardado
            // (localStorage limpo, app reinstalado, navegador interno de outro app).
            // O app manda o modelo em device_name (ex.: "ALT-LX2", "iPhone"), que é estável.
            // Nesse caso reaproveita a linha existente trocando o UUID, em vez de
            // bloquear o cliente e criar uma linha órfã a cada perda de storage.
            if ($this->nomeIdentificaAparelho($deviceName)) {
                $mesmoAparelho = UserDevice::where('user_id', $user->id)
                    ->where('device_type', 'mobile_app')
                    ->where('is_blocked', false)
                    ->where('device_name', $deviceName)
                    ->orderByDesc('last_used_at')
                    ->lockForUpdate()
                    ->first();

                if ($mesmoAparelho) {
                    $this->liberarVagasDoMesmoAparelho($user->id, $deviceName, $mesmoAparelho->id);

                    $mesmoAparelho->update([
                        'device_uuid'  => $deviceUuid,
                        'is_active'    => true,
                        'last_used_at' => now(),
                    ]);

                    $device      = $mesmoAparelho->fresh();
                    $uuidTrocado = true;
                    $motivoTroca = 'nome';
                    return;
                }
            }

            // Rede de segurança para quando o nome NÃO identifica o aparelho.
            // O app só consegue o modelo pelo Client Hints; quando isso falha (WebView,
            // UA reduzido "Android 10; K"), ele manda o genérico "Android" e o match por
            // device_name acima não acha nada — mesmo sendo o mesmo celular de sempre.
            // Como o limite é de UM celular por conta, um UUID novo aqui é muito mais
            // provavelmente o aparelho que perdeu o storage do que um segundo aparelho.
            $mobiles = UserDevice::where('user_id', $user->id)
                ->where('device_type', 'mobile_app')
                ->where('is_blocked', false)
                ->lockForUpdate()
                ->get();

            if ($mobiles->count() === 1) {
                $unico = $mobiles->first();

                $unico->update([
                    'device_uuid'  => $deviceUuid,
                    'is_active'    => true,
                    'last_used_at' => now(),
                ]);

                $device      = $unico->fresh();
                $uuidTrocado = true;
                $motivoTroca = 'vaga unica';
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

        // O aparelho voltou com outro UUID: derruba os tokens mobile antigos para que
        // continue valendo uma sessão de celular só (se fossem duas pessoas, uma derruba a outra)
        if ($uuidTrocado) {
            $user->tokens()->where('name', 'like', 'mobile\_%')->delete();

            // Log::info é engolido enquanto o LOG_LEVEL do .env estiver em 'error' —
            // a prova confiável é a coluna os de login_sessions, gravada logo abaixo.
            Log::info('[mobile] device_uuid reatribuido', [
                'user_id'     => $user->id,
                'device_id'   => $device->id,
                'device_name' => $deviceName,
                'novo_uuid'   => $deviceUuid,
                'motivo'      => $motivoTroca,
                'ip'          => $ip,
            ]);
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
            // Consultar sempre com LIKE 'UUID reatribuido%' para pegar os dois motivos
            'os'           => $uuidTrocado ? 'UUID reatribuido (' . $motivoTroca . ')' : null,
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
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'uf_preferencia' => $user->uf_preferencia ?? 'GO',
                'layout_id'      => $user->layout_id ?? 1,
                'imagem'         => $user->imagem ? asset('storage/' . $user->imagem) : null,
            ],
        ]);
    }

    /**
     * Desativa as outras linhas mobile_app do MESMO aparelho (mesmo device_name),
     * para que sobras de UUIDs antigos não ocupem a vaga do próprio dono.
     */
    private function liberarVagasDoMesmoAparelho(int $userId, string $deviceName, int $manterId): void
    {
        if (!$this->nomeIdentificaAparelho($deviceName)) {
            return;
        }

        UserDevice::where('user_id', $userId)
            ->where('device_type', 'mobile_app')
            ->where('device_name', $deviceName)
            ->where('is_active', true)
            ->where('id', '!=', $manterId)
            ->update(['is_active' => false]);
    }

    /**
     * O app manda o modelo do aparelho em device_name (ex.: "ALT-LX2", "iPhone"). Só serve
     * como identificação se não for o fallback genérico nem um User-Agent inteiro.
     */
    private function nomeIdentificaAparelho(string $deviceName): bool
    {
        $nome = trim($deviceName);

        if (mb_strlen($nome) < 3 || mb_strlen($nome) > 60) {
            return false;
        }

        // Genéricos: "App Mobile" é o fallback do servidor, "Android" é o do app
        // (useAuth.ts, quando o Client Hints não devolve o modelo). Aceitar "Android"
        // faria dois celulares diferentes da mesma conta serem tratados como o mesmo
        // aparelho, e liberarVagasDoMesmoAparelho() desativaria um deles.
        if (strcasecmp($nome, 'App Mobile') === 0 || strcasecmp($nome, 'Android') === 0) {
            return false;
        }

        // User-Agent completo (fallback da linha do $deviceName) não identifica o aparelho
        return !preg_match('/Mozilla|AppleWebKit|Android \d|\(/i', $nome);
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
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'uf_preferencia' => $user->uf_preferencia ?? 'GO',
                'layout_id'      => $user->layout_id ?? 1,
                'imagem'         => $user->imagem ? asset('storage/' . $user->imagem) : null,
            ],
        ]);
    }
}
