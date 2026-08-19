<?php

namespace App\Http\Controllers;

use App\Models\Assinatura;
use App\Models\Humana\HumanaAcesso;
use Illuminate\Http\Request;

/**
 * Gestão (só desenvolvedor) de quem pode usar o módulo Humana.
 * A liberação é por ASSINATURA: vale para o titular e toda a equipe dela.
 */
class HumanaAcessosController extends Controller
{
    public function index()
    {
        $assinaturas = Assinatura::with('user:id,name,email')
            ->orderBy('id')
            ->get(['id', 'user_id', 'status']);

        $liberadas = HumanaAcesso::where('ativo', true)->pluck('assinatura_id')->flip();

        return view('humana.acessos', compact('assinaturas', 'liberadas'));
    }

    public function toggle(Request $request)
    {
        $dados = $request->validate(['assinatura_id' => 'required|integer|exists:assinaturas,id']);

        $acesso = HumanaAcesso::firstOrNew(['assinatura_id' => $dados['assinatura_id']]);
        $acesso->ativo = !($acesso->exists && $acesso->ativo);
        $acesso->save();

        return back()->with('status', sprintf(
            'Assinatura %d %s no módulo Humana.',
            $acesso->assinatura_id,
            $acesso->ativo ? 'LIBERADA' : 'bloqueada'
        ));
    }
}
