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
        $horas = (int) ($request->input("horas", 48));
        $horas = in_array($horas, [24, 48, 72, 168]) ? $horas : 48;

        $suspeitos = \DB::table("login_sessions as ls")
            ->join("users", "users.id", "=", "ls.user_id")
            ->where("ls.logged_in_at", ">=", now()->subHours($horas))
            ->groupBy("ls.user_id", "users.name", "users.email", "users.status")
            ->havingRaw("COUNT(DISTINCT ls.device_fingerprint) > 1")
            ->selectRaw("ls.user_id, users.name, users.email, users.status, COUNT(DISTINCT ls.device_fingerprint) as dispositivos_distintos, COUNT(*) as total_logins, MAX(ls.logged_in_at) as ultimo_login")
            ->orderByDesc("dispositivos_distintos")
            ->get();

        $detalhesPorUsuario = [];
        foreach ($suspeitos as $s) {
            $detalhesPorUsuario[$s->user_id] = \App\Models\LoginSession::where("user_id", $s->user_id)
                ->where("logged_in_at", ">=", now()->subHours($horas))
                ->orderByDesc("logged_in_at")
                ->get();
        }

        $ipsBlockeados = BlockedIp::pluck('ip_address')->flip();

        // Conta quantos usuários distintos estão ativos por IP (para o aviso de bloqueio)
        $sessoesPorIp = \DB::table('sessions')
            ->select('ip_address', \DB::raw('count(distinct user_id) as total'))
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->pluck('total', 'ip_address');

        // Combinações user_id+ip já bloqueadas individualmente
        $userIpsBlockeados = BlockedUserIp::select('user_id', 'ip_address')
            ->get()
            ->mapWithKeys(fn($r) => ["{$r->user_id}_{$r->ip_address}" => true]);

        // Papel de cada suspeito na assinatura (titular ou não)
        $userIds = $suspeitos->pluck('user_id');

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

        return view("gerenciamento.logins-compartilhados", compact("suspeitos", "detalhesPorUsuario", "horas", "ipsBlockeados", "sessoesPorIp", "userIpsBlockeados", "emailAssins", "titularesPorAssinatura"));
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

        \DB::table('sessions')->where('user_id', $user->id)->delete();
        LoginSession::where('user_id', $user->id)->delete();

        return redirect()
            ->route('gerenciamento.logins-compartilhados', ['horas' => $horas])
            ->with('limpo', "Histórico de sessões de \"{$user->name}\" apagado.");
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
