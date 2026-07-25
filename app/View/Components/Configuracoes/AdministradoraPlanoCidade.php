<?php

namespace App\View\Components\configuracoes;

use App\Models\Administradora;
use App\Models\AdministradoraPlano;
use App\Models\Assinatura;
use App\Models\Plano;
use App\Models\TabelaOrigens;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AdministradoraPlanoCidade extends Component
{
    public $administradoras;
    public $planos;
    public $tabelas;
    public $assinaturas;
    public $assinaturasVinculadas;

    public function __construct()
    {
        $this->assinaturas = Assinatura::with('user')->latest()->get();
        $this->administradoras = Administradora::all();
        $this->planos = Plano::all();
        $this->tabelas = TabelaOrigens::all();

        // Só o resumo por assinatura — os vínculos em si são carregados via AJAX
        // ao clicar em "Ver Vínculos" (são +12 mil registros no total).
        $this->assinaturasVinculadas = AdministradoraPlano::query()
            ->select('assinatura_id')
            ->selectRaw('COUNT(*) as total_vinculos')
            ->whereNotNull('assinatura_id')
            ->groupBy('assinatura_id')
            ->orderBy('assinatura_id')
            ->with('assinatura.user')
            ->get();
    }





    public function render(): View|Closure|string
    {
        return view('components.configuracoes.administradora-plano-cidade');
    }
}
