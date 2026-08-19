<?php

namespace App\Http\Controllers;

use App\Models\Humana\HumanaAcesso;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Gestão (só desenvolvedor) de quem pode usar o módulo Humana.
 * A liberação é POR USUÁRIO — liberar um titular NÃO libera a equipe dele.
 */
class HumanaAcessosController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('name')->get(['id', 'name', 'email', 'status']);

        $liberados = HumanaAcesso::where('ativo', true)->pluck('user_id')->flip();

        return view('humana.acessos', compact('usuarios', 'liberados'));
    }

    public function toggle(Request $request)
    {
        $dados = $request->validate(['user_id' => 'required|integer|exists:users,id']);

        $acesso = HumanaAcesso::firstOrNew(['user_id' => $dados['user_id']]);
        $acesso->ativo = !($acesso->exists && $acesso->ativo);
        $acesso->save();

        $usuario = User::find($dados['user_id']);

        return back()->with('status', sprintf(
            '%s (%s) %s no módulo Humana.',
            $usuario->name,
            $usuario->email,
            $acesso->ativo ? 'LIBERADO' : 'bloqueado'
        ));
    }
}
