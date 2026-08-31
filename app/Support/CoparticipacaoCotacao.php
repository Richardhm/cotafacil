<?php

namespace App\Support;

use App\Models\Pdf;
use App\Models\PdfExcecao;

/**
 * Dados do bloco de coparticipação da cotação Hapvida (tabelinhas "Copart
 * Total" e "Coparticipação somente em Terapias"), com a MESMA cascata de
 * busca do DashboardController@criarPDF: exceção da cidade > (plano+cidade+
 * administradora) > (plano+cidade) > (plano). Usado pelo Hapvida Super
 * Simples e pela Tabela Completa, que não tinham o bloco.
 */
class CoparticipacaoCotacao
{
    /** @return array{pdf: ?object, quantidade_copar: int, status_excecao: bool, linha_01: string, linha_02: string} */
    public static function montar(int $plano, int $cidade, ?int $administradora = null): array
    {
        $resultado = ['pdf' => null, 'quantidade_copar' => 0, 'status_excecao' => false, 'linha_01' => '', 'linha_02' => ''];

        $excecao = PdfExcecao::where('plano_id', $plano)->where('tabela_origens_id', $cidade)->first();
        if ($excecao) {
            $resultado['pdf'] = $excecao;
            $resultado['quantidade_copar'] = 1;
            $resultado['status_excecao'] = true;
            return $resultado;
        }

        $pdf = null;
        if ($administradora) {
            $pdf = Pdf::where('plano_id', $plano)->where('tabela_origens_id', $cidade)
                ->where('administradora_id', $administradora)->first();
        }
        $pdf = $pdf
            ?? Pdf::where('plano_id', $plano)->where('tabela_origens_id', $cidade)->first()
            ?? Pdf::where('plano_id', $plano)->first();

        if (!$pdf) {
            return $resultado;
        }

        $resultado['pdf'] = $pdf;
        // Como no dashboard: match por cidade libera o bloco; só-plano libera
        // se houver linha02 (as duas linhas do "somente em Terapias")
        $porCidade = $pdf->tabela_origens_id == $cidade;
        if ($porCidade) {
            $resultado['quantidade_copar'] = 1;
        }
        if (!empty($pdf->linha02)) {
            $resultado['quantidade_copar'] = 1;
            $itens = array_map('trim', explode('|', $pdf->linha02));
            $resultado['linha_01'] = $itens[0] ?? '';
            $resultado['linha_02'] = $itens[1] ?? '';
        }

        return $resultado;
    }
}
