<?php

namespace App\Http\Controllers;

use App\Models\RotuloCotacao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Aba "Rótulos" de /configuracoes (só desenvolvedor): cadastra, edita o texto
 * e remove rótulos personalizados da cotação por usuário ou por assinatura.
 */
class RotulosCotacaoController extends Controller
{
    public function store(Request $request)
    {
        $dados = $request->validate([
            'nivel'         => 'required|in:usuario,assinatura',
            'user_id'       => 'required_if:nivel,usuario|nullable|integer|exists:users,id',
            'assinatura_id' => 'required_if:nivel,assinatura|nullable|integer|exists:assinaturas,id',
            'chave'         => ['required', Rule::in(array_keys(RotuloCotacao::CHAVES))],
            'plano_id'      => 'required_if:chave,nome_plano|nullable|integer|exists:planos,id',
            'texto'         => 'required|string|max:40',
        ]);

        $alvo = $dados['nivel'] === 'usuario'
            ? ['user_id' => $dados['user_id'], 'assinatura_id' => null]
            : ['user_id' => null, 'assinatura_id' => $dados['assinatura_id']];

        // Um rótulo por (alvo, chave, plano): cadastrar de novo = substituir
        RotuloCotacao::updateOrCreate(
            $alvo + [
                'chave'    => $dados['chave'],
                'plano_id' => $dados['chave'] === 'nome_plano' ? $dados['plano_id'] : null,
            ],
            ['texto' => mb_strtoupper(trim($dados['texto']))]
        );

        return redirect(route('configuracoes.index') . '#tab13')->with('success', 'Rótulo salvo.');
    }

    public function texto(RotuloCotacao $rotulo, Request $request)
    {
        $dados = $request->validate(['texto' => 'required|string|max:40']);
        $rotulo->update(['texto' => mb_strtoupper(trim($dados['texto']))]);

        return redirect(route('configuracoes.index') . '#tab13')->with('success', 'Rótulo atualizado.');
    }

    public function destroy(RotuloCotacao $rotulo)
    {
        $rotulo->delete();

        return redirect(route('configuracoes.index') . '#tab13')->with('success', 'Rótulo removido (volta ao padrão).');
    }
}
