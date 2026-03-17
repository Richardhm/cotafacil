<?php

namespace App\Http\Controllers;

use App\Models\Assinatura;
use App\Models\Cupom;
use App\Models\EmailAssinatura;
use App\Models\Layout;
use App\Models\TabelaOrigens;
use App\Models\TipoPlano;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('gerenciamento.index',compact('users','assinatura','valor','extra','layouts','folder','user','cidades'));
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






}
