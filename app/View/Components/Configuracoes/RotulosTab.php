<?php

namespace App\View\Components\Configuracoes;

use App\Models\Assinatura;
use App\Models\Plano;
use App\Models\RotuloCotacao;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RotulosTab extends Component
{
    public $rotulos;
    public $usuarios;
    public $assinaturas;
    public $planos;
    public $chaves;

    public function __construct()
    {
        $this->rotulos     = RotuloCotacao::with(['user:id,name,email', 'assinatura.user:id,name,email', 'plano:id,nome'])
            ->orderBy('assinatura_id')->orderBy('user_id')->orderBy('chave')->get();
        $this->usuarios    = User::orderBy('name')->get(['id', 'name', 'email']);
        $this->assinaturas = Assinatura::with('user:id,name,email')->orderBy('id')->get(['id', 'user_id']);
        $this->planos      = Plano::orderBy('nome')->get(['id', 'nome']);
        $this->chaves      = RotuloCotacao::CHAVES;
    }

    public function render(): View|Closure|string
    {
        return view('components.configuracoes.rotulos-tab');
    }
}
