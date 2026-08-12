<?php

namespace App\Http\Controllers;

use App\Models\Assinatura;
use App\Models\BlockedIp;
use App\Models\BlockedUserIp;
use App\Models\PendingTeamUser;
use App\Models\Cupom;
use App\Models\EmailAssinatura;
use App\Models\Layout;
use App\Models\LoginSession;
use App\Models\TabelaOrigens;
use App\Models\TipoPlano;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class GerenciadorController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        if($user->primeiro_acesso == 0) {
            $user->primeiro_acesso = 1;
            $user->save();
        }
        $assinatura_id = Assinatura::where("user_id",auth()->user()->id)->first()->id;
        $users = User::whereIn(
            'id',
            EmailAssinatura::where('assinatura_id', $assinatura_id)

                ->pluck('user_id')
        )
            ->orderBy("id","desc")
            ->get();

        $assinatura = Assinatura::where("user_id",auth()->user()->id)->first();
        /**Layout**/
        $assinaturas = Assinatura::find(auth()->user()->assinaturas()->first()->id);
        $folder = "";
        if($assinaturas->folder) {
            $folder = $assinaturas->folder;
        }
        $layouts = Layout::all();

        /**Fim Layout**/




        if($assinatura->status == "trial") {
            $plan = TipoPlano::find($assinatura->tipo_plano_id);
            $valor = $assinatura->preco_total;
            $limite_gratuito = 1; // 5 por padrão
            $extra = number_format($plan->valor_por_email,2,",",".");
        } elseif($assinatura->cupom_id and empty($assinatura->tipo_plano_id)) {
            $cupom = Cupom::find($assinatura->cupom_id);
            $valor = $assinatura->preco_total;
            $limite_gratuito = $assinatura->emails_permitidos;
            $extra = 29.90 - $cupom->desconto_extra;
        } elseif (empty($assinatura->cupom_id) && in_array($assinatura->tipo_plano_id, [1, 2])) {
            $plan = TipoPlano::find($assinatura->tipo_plano_id);
            $valor = $assinatura->preco_total;
            $limite_gratuito = $assinatura->emails_permitidos; // 5 por padrão
            $extra = number_format($plan->valor_por_email,2,",",".");
        }

        $user = auth()->user();

        $cidades = TabelaOrigens::orderBy('uf')->get()->groupBy('uf');

        $pendentes = PendingTeamUser::where('admin_user_id', Auth::id())
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('gerenciamento.index', compact('users','assinatura','valor','extra','layouts','folder','user','cidades','pendentes'));
    }

    public function regiao(Request $request)
    {
        $user = Auth::user(); // ou auth()->user()
        $uf = $request->input('regiao');
        $user->uf_preferencia = $uf ?: null;
        if($user->save()) {
            return true;
        } else {
            return false;
        }
        //return back()->with('status', 'Região atualizada com sucesso!');
    }







    public function loginsCompartilhados(\Illuminate\Http\Request $request)
    {
        $horasInput = $request->input("horas", 48);

        if ($horasInput === 'hoje') {
            $horas = 'hoje';
            $desde = \Carbon\Carbon::today();
        } else {
            $horas = in_array((int) $horasInput, [24, 48, 72, 168]) ? (int) $horasInput : 48;
            $desde = now()->subHours($horas);
        }

        // Usuários com 2+ dispositivos (ativos ou bloqueados) usados no período
        $suspeitos = \DB::table("user_devices as ud")
            ->join("users", "users.id", "=", "ud.user_id")
            ->where(function ($q) use ($desde) {
                $q->where("ud.last_used_at", ">=", $desde)
                  ->orWhere("ud.registered_at", ">=", $desde);
            })
            ->groupBy("ud.user_id", "users.name", "users.email", "users.status", "users.max_desktops", "users.max_mobiles")
            ->havingRaw("COUNT(ud.id) >= 1")
            ->selectRaw("ud.user_id, users.name, users.email, users.status, users.max_desktops, users.max_mobiles, COUNT(ud.id) as dispositivos_distintos, MAX(COALESCE(ud.last_used_at, ud.registered_at)) as ultimo_login")
            ->orderByDesc("dispositivos_distintos")
            ->get();

        $userIds = $suspeitos->pluck('user_id');

        // Total de logins bem-sucedidos no período (não conta tentativas bloqueadas)
        $loginsCount = \DB::table('login_sessions')
            ->whereIn('user_id', $userIds)
            ->where('logged_in_at', '>=', $desde)
            ->where('was_blocked', false)
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as total')
            ->pluck('total', 'user_id');

        $suspeitos = $suspeitos->map(function ($s) use ($loginsCount) {
            $s->total_logins = $loginsCount[$s->user_id] ?? 0;
            return $s;
        });

        $detalhesPorUsuario = [];
        $tentativasBloqueadasPorUsuario = [];

        foreach ($suspeitos as $s) {
            // Último login bem-sucedido por device_uuid
            $ultimasSessoes = \DB::table('login_sessions')
                ->where('user_id', $s->user_id)
                ->where('was_blocked', false)
                ->whereIn('id', function ($sub) use ($s) {
                    $sub->selectRaw('MAX(id)')
                        ->from('login_sessions')
                        ->where('user_id', $s->user_id)
                        ->where('was_blocked', false)
                        ->groupBy('device_uuid');
                })
                ->get()
                ->keyBy('device_uuid');

            $devices = \App\Models\UserDevice::where('user_id', $s->user_id)
                ->where(function ($q) use ($desde) {
                    $q->where('last_used_at', '>=', $desde)
                      ->orWhere('registered_at', '>=', $desde);
                })
                ->orderByDesc('is_active')
                ->orderByDesc('last_used_at')
                ->get();

            $detalhesPorUsuario[$s->user_id] = $devices->map(function ($d) use ($ultimasSessoes) {
                $sess  = $ultimasSessoes[$d->device_uuid] ?? null;
                $parts = explode(' em ', $d->device_name ?? '', 2);
                $isMobileApp = $d->device_type === 'mobile_app';
                return (object) [
                    'device_id'         => $d->id,
                    'device_uuid'       => $d->device_uuid,
                    'device_type'       => $d->device_type ?? 'desktop',
                    'is_active'         => (bool) $d->is_active,
                    'is_blocked'        => $d->is_blocked,
                    'browser'           => $isMobileApp ? 'App Mobile' : ($sess->browser ?? ($parts[0] ?: null)),
                    'os'                => $sess->os ?? ($parts[1] ?? null),
                    'device_model'      => $isMobileApp ? ($d->device_name ?: ($sess->device_model ?? $d->device_model)) : ($sess->device_model ?? $d->device_model),
                    'is_mobile'         => $isMobileApp || (bool) ($sess->is_mobile ?? false),
                    'ip_address'        => $sess->ip_address ?? null,
                    'city'              => $sess->city ?? null,
                    'country'           => $sess->country ?? null,
                    'screen_resolution' => $sess->screen_resolution ?? null,
                    'gpu_renderer'      => $sess->gpu_renderer ?? null,
                    'last_seen_at'      => $d->last_used_at,
                    'was_displaced'     => (bool) ($sess->was_displaced ?? false),
                ];
            });

            // Tentativas bloqueadas (limite de dispositivos atingido)
            $tentativasBloqueadasPorUsuario[$s->user_id] = \App\Models\LoginSession::where('user_id', $s->user_id)
                ->where('was_blocked', true)
                ->orderByDesc('logged_in_at')
                ->limit(10)
                ->get();
        }

        $ipsBlockeados = BlockedIp::pluck('ip_address')->flip();

        $sessoesPorIp = \DB::table('sessions')
            ->select('ip_address', \DB::raw('count(distinct user_id) as total'))
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->pluck('total', 'ip_address');

        $userIpsBlockeados = BlockedUserIp::select('user_id', 'ip_address')
            ->get()
            ->mapWithKeys(fn($r) => ["{$r->user_id}_{$r->ip_address}" => true]);

        $emailAssins = \DB::table('emails_assinatura')
            ->whereIn('user_id', $userIds)
            ->select('user_id', 'assinatura_id', 'is_administrador')
            ->get()
            ->keyBy('user_id');

        $assinaturasNaoAdmin = $emailAssins->where('is_administrador', 0)->pluck('assinatura_id')->unique();

        $titularesPorAssinatura = \DB::table('emails_assinatura as ea')
            ->join('users', 'users.id', '=', 'ea.user_id')
            ->whereIn('ea.assinatura_id', $assinaturasNaoAdmin)
            ->where('ea.is_administrador', true)
            ->select('ea.assinatura_id', 'users.name', 'users.email')
            ->get()
            ->keyBy('assinatura_id');

        return view("gerenciamento.logins-compartilhados", compact(
            "suspeitos", "detalhesPorUsuario", "tentativasBloqueadasPorUsuario",
            "horas", "ipsBlockeados", "sessoesPorIp", "userIpsBlockeados",
            "emailAssins", "titularesPorAssinatura"
        ));
    }

    /**
     * Ajusta quantos aparelhos a conta pode ter ativos ao mesmo tempo.
     *
     * Substitui o UPDATE manual em user_devices que era feito toda vez que um
     * cliente precisava de um aparelho a mais. Não mexe nos dispositivos já
     * cadastrados: só muda o teto. Se o limite for reduzido abaixo do que já
     * está ativo, ninguém é desconectado — o excedente simplesmente não
     * consegue registrar aparelho novo até alguém ser removido.
     */
    public function limitesDispositivos(User $user, \Illuminate\Http\Request $request)
    {
        $dados = $request->validate([
            'max_desktops' => 'required|integer|min:1|max:20',
            'max_mobiles'  => 'required|integer|min:1|max:20',
        ], [], [
            'max_desktops' => 'limite de computadores',
            'max_mobiles'  => 'limite de celulares',
        ]);

        $user->update([
            'max_desktops' => $dados['max_desktops'],
            'max_mobiles'  => $dados['max_mobiles'],
        ]);

        return back()->with(
            'success',
            "Limites de {$user->name} atualizados: {$dados['max_desktops']} computador(es) e {$dados['max_mobiles']} celular(es)."
        );
    }

    public function removerDispositivo(\App\Models\UserDevice $device, \Illuminate\Http\Request $request)
    {
        $horas = $request->input('horas', 48);
        $user  = User::find($device->user_id);

        $sessoes = LoginSession::where('user_id', $device->user_id)
            ->where('device_uuid', $device->device_uuid)
            ->whereNull('logged_out_at')
            ->pluck('session_id');

        if ($sessoes->isNotEmpty()) {
            LoginSession::where('user_id', $device->user_id)
                ->where('device_uuid', $device->device_uuid)
                ->whereNull('logged_out_at')
                ->update(['logged_out_at' => now()]);
            \DB::table('sessions')->whereIn('id', $sessoes)->delete();
        }

        $device->update(['is_active' => false]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('gerenciamento.logins-compartilhados', ['horas' => $horas])
            ->with('dispositivo_removido', "Dispositivo de \"{$user->name}\" removido. Uma vaga foi liberada.");
    }

    public function bloquearDispositivo(\App\Models\UserDevice $device, \Illuminate\Http\Request $request)
    {
        $horas = $request->input('horas', 48);
        $user  = User::find($device->user_id);

        $sessoes = LoginSession::where('user_id', $device->user_id)
            ->where('device_uuid', $device->device_uuid)
            ->whereNull('logged_out_at')
            ->pluck('session_id');

        if ($sessoes->isNotEmpty()) {
            LoginSession::where('user_id', $device->user_id)
                ->where('device_uuid', $device->device_uuid)
                ->whereNull('logged_out_at')
                ->update(['logged_out_at' => now()]);
            \DB::table('sessions')->whereIn('id', $sessoes)->delete();
        }

        $device->update(['is_active' => false, 'is_blocked' => true]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('gerenciamento.logins-compartilhados', ['horas' => $horas])
            ->with('dispositivo_bloqueado', "Dispositivo de \"{$user->name}\" bloqueado. Não poderá se registrar novamente.");
    }

    public function desbloquearDispositivo(\App\Models\UserDevice $device, \Illuminate\Http\Request $request)
    {
        $horas = $request->input('horas', 48);
        $user  = User::find($device->user_id);

        $device->update(['is_active' => true, 'is_blocked' => false]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('gerenciamento.logins-compartilhados', ['horas' => $horas])
            ->with('dispositivo_desbloqueado', "Dispositivo de \"{$user->name}\" desbloqueado.");
    }

    public function liberarTitular(User $user, \Illuminate\Http\Request $request)
    {
        $horas = $request->input('horas', 48);

        $deviceUuids = \App\Models\UserDevice::where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('device_uuid');

        if ($deviceUuids->isNotEmpty()) {
            $sessoes = LoginSession::where('user_id', $user->id)
                ->whereIn('device_uuid', $deviceUuids)
                ->whereNull('logged_out_at')
                ->pluck('session_id');

            if ($sessoes->isNotEmpty()) {
                LoginSession::where('user_id', $user->id)
                    ->whereIn('device_uuid', $deviceUuids)
                    ->whereNull('logged_out_at')
                    ->update(['logged_out_at' => now()]);
                \DB::table('sessions')->whereIn('id', $sessoes)->delete();
            }

            \App\Models\UserDevice::where('user_id', $user->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('gerenciamento.logins-compartilhados', ['horas' => $horas])
            ->with('titular_liberado', "Dispositivos de \"{$user->name}\" liberados. O titular pode se logar novamente.");
    }

    public function desativarLoginCompartilhado(User $user, \Illuminate\Http\Request $request)
    {
        $horas = $request->input('horas', 48);

        $user->status = 2;
        $user->save();

        // Encerra todas as sessões ativas do usuário imediatamente
        LoginSession::where('user_id', $user->id)
            ->whereNull('logged_out_at')
            ->update(['logged_out_at' => now()]);

        \DB::table('sessions')->where('user_id', $user->id)->delete();

        return redirect()
            ->route('gerenciamento.logins-compartilhados', ['horas' => $horas])
            ->with('desativado', "Usuário \"{$user->name}\" desativado por login compartilhado.");
    }

    public function ativarUsuario(User $user, \Illuminate\Http\Request $request)
    {
        $horas = $request->input('horas', 48);

        $user->status = 1;
        $user->save();

        return redirect()
            ->route('gerenciamento.logins-compartilhados', ['horas' => $horas])
            ->with('ativado', "Usuário \"{$user->name}\" reativado com sucesso.");
    }

    public function bloquearIp(Request $request)
    {
        $ip    = $request->input('ip_address');
        $horas = $request->input('horas', 48);

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return back()->withErrors(['error' => 'IP inválido.']);
        }

        BlockedIp::firstOrCreate(['ip_address' => $ip]);
        Cache::forget("blocked_ip_{$ip}");

        // Derruba sessões ativas vindas desse IP imediatamente
        $sessionIds = \DB::table('sessions')->where('ip_address', $ip)->pluck('id');
        if ($sessionIds->isNotEmpty()) {
            LoginSession::whereIn('session_id', $sessionIds)
                ->whereNull('logged_out_at')
                ->update(['logged_out_at' => now()]);
            \DB::table('sessions')->where('ip_address', $ip)->delete();
        }

        return redirect()
            ->route('gerenciamento.logins-compartilhados', ['horas' => $horas])
            ->with('ip_bloqueado', "IP {$ip} bloqueado. Sessões encerradas.");
    }

    public function desbloquearIp(Request $request)
    {
        $ip    = $request->input('ip_address');
        $horas = $request->input('horas', 48);

        BlockedIp::where('ip_address', $ip)->delete();
        Cache::forget("blocked_ip_{$ip}");

        return redirect()
            ->route('gerenciamento.logins-compartilhados', ['horas' => $horas])
            ->with('ip_desbloqueado', "IP {$ip} desbloqueado.");
    }

    public function limparSessoes(User $user, \Illuminate\Http\Request $request)
    {
        $horas = $request->input('horas', 48);
        $currentSession = session()->getId();

        // Preserva a sessão atual para não deslogar o administrador
        \DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSession)
            ->delete();

        LoginSession::where('user_id', $user->id)->delete();
        \App\Models\UserDevice::where('user_id', $user->id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('gerenciamento.logins-compartilhados', ['horas' => $horas])
            ->with('limpo', "Histórico de sessões e dispositivos de \"{$user->name}\" apagado.");
    }

    public function bloquearUsuarioIp(Request $request)
    {
        $userId = $request->input('user_id');
        $ip     = $request->input('ip_address');
        $horas  = $request->input('horas', 48);

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return back()->withErrors(['error' => 'IP inválido.']);
        }

        BlockedUserIp::firstOrCreate(['user_id' => $userId, 'ip_address' => $ip]);
        Cache::forget("blocked_user_ip_{$userId}_{$ip}");

        // Derruba apenas as sessões desse usuário vindas desse IP
        $sessionIds = \DB::table('sessions')
            ->where('user_id', $userId)
            ->where('ip_address', $ip)
            ->pluck('id');

        if ($sessionIds->isNotEmpty()) {
            LoginSession::whereIn('session_id', $sessionIds)
                ->whereNull('logged_out_at')
                ->update(['logged_out_at' => now()]);
            \DB::table('sessions')
                ->where('user_id', $userId)
                ->where('ip_address', $ip)
                ->delete();
        }

        $user = User::find($userId);
        return redirect()
            ->route('gerenciamento.logins-compartilhados', ['horas' => $horas])
            ->with('usuario_ip_bloqueado', "Usuário \"{$user->name}\" bloqueado para o IP {$ip}. Os demais usuários deste IP não foram afetados.");
    }

    public function desbloquearUsuarioIp(Request $request)
    {
        $userId = $request->input('user_id');
        $ip     = $request->input('ip_address');
        $horas  = $request->input('horas', 48);

        BlockedUserIp::where('user_id', $userId)->where('ip_address', $ip)->delete();
        Cache::forget("blocked_user_ip_{$userId}_{$ip}");

        $user = User::find($userId);
        return redirect()
            ->route('gerenciamento.logins-compartilhados', ['horas' => $horas])
            ->with('usuario_ip_desbloqueado', "Bloqueio individual removido para \"{$user->name}\" neste IP.");
    }
}
