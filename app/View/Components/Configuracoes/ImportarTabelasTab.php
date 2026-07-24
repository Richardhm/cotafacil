<?php

namespace App\View\Components\Configuracoes;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ImportarTabelasTab extends Component
{
    public function render(): View|Closure|string
    {
        return view('components.configuracoes.importar-tabelas-tab');
    }
}
