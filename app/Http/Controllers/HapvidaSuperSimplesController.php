<?php

namespace App\Http\Controllers;

use App\Models\Plano;
use App\Models\Tabela;
use App\Models\TabelaOrigens;
use Barryvdh\DomPDF\Facade\Pdf as PDFFile;
use Illuminate\Http\Request;

class HapvidaSuperSimplesController extends Controller
{
    private const HAPVIDA_ID = 4;
    private const SUPER_SIMPLES_ID = 5;

    public function index()
    {
        $cidadeIds = Tabela::where('administradora_id', self::HAPVIDA_ID)
            ->where('plano_id', self::SUPER_SIMPLES_ID)
            ->pluck('tabela_origens_id')
            ->unique();

        $estados = TabelaOrigens::whereIn('id', $cidadeIds)
            ->groupBy('uf')
            ->select('uf')
            ->orderBy('uf')
            ->get();

        $ufpreferencia = auth()->user()->uf_preferencia ?? '';

        return view('hapvida-super-simples.index', compact('estados', 'ufpreferencia'))
            ->with('hapvidaId', self::HAPVIDA_ID)
            ->with('superSimplesId', self::SUPER_SIMPLES_ID);
    }

    public function getCidades(Request $request)
    {
        $uf = $request->input('uf');

        $cidadeIds = Tabela::where('administradora_id', self::HAPVIDA_ID)
            ->where('plano_id', self::SUPER_SIMPLES_ID)
            ->pluck('tabela_origens_id')
            ->unique();

        $cidades = TabelaOrigens::whereIn('id', $cidadeIds)
            ->where('uf', $uf)
            ->select('id', 'nome')
            ->orderBy('nome')
            ->get();

        return response()->json($cidades);
    }

    public function cotacao(Request $request)
    {
        $cidade = $request->input('tabela_origem');
        $faixasInput = $request->input('faixas')[0];

        $sqlCase = '';
        $faixasIds = [];
        foreach ($faixasInput as $faixaId => $quantidade) {
            if ($quantidade > 0) {
                $sqlCase .= " WHEN tabelas.faixa_etaria_id = {$faixaId} THEN {$quantidade}";
                $faixasIds[] = $faixaId;
            }
        }

        if (empty($faixasIds)) {
            return response()->json(['message' => 'Nenhuma faixa etária válida fornecida.'], 422);
        }

        $cenarios = [
            ['label' => 'Com Copart - Com Odonto', 'copart' => 1, 'odonto' => 1],
            ['label' => 'Sem Copart - Com Odonto', 'copart' => 0, 'odonto' => 1],
            ['label' => 'Com Copart - Sem Odonto', 'copart' => 1, 'odonto' => 0],
            ['label' => 'Sem Copart - Sem Odonto', 'copart' => 0, 'odonto' => 0],
        ];

        $resultados = [];

        foreach ($cenarios as $cenario) {
            $dadosTabela = Tabela::select('tabelas.*')
                ->selectRaw("CASE {$sqlCase} END AS quantidade")
                ->where('tabelas.tabela_origens_id', $cidade)
                ->where('tabelas.plano_id', self::SUPER_SIMPLES_ID)
                ->where('tabelas.administradora_id', self::HAPVIDA_ID)
                ->where('tabelas.coparticipacao', $cenario['copart'])
                ->where('tabelas.odonto', $cenario['odonto'])
                ->where('tabelas.acomodacao_id', '!=', 3)
                ->whereIn('tabelas.faixa_etaria_id', $faixasIds)
                ->get();

            $dadosAgrupados = $this->agruparPorFaixaEtaria($dadosTabela);
            $totais = $this->calcularTotais($dadosAgrupados);

            if (!empty($totais['rows'])) {
                $resultados[] = [
                    'label'             => $cenario['label'],
                    'rows'              => $totais['rows'],
                    'copart'            => $cenario['copart'],
                    'odonto'            => $cenario['odonto'],
                    'total_apartamento' => $totais['total_apartamento'],
                    'total_enfermaria'  => $totais['total_enfermaria'],
                ];
            }
        }

        return view('hapvida-super-simples.cards', ['resultados' => $resultados])->render();
    }

    private function agruparPorFaixaEtaria($dadosTabela)
    {
        $dadosAgrupados = [];

        foreach ($dadosTabela as $dado) {
            $faixaId = $dado->faixa_etaria_id;

            if (!isset($dadosAgrupados[$faixaId])) {
                $dadosAgrupados[$faixaId] = [
                    'faixa_etaria'      => "Faixa {$faixaId}",
                    'quantidade'        => $dado->quantidade,
                    'valor_apartamento' => 0,
                    'valor_enfermaria'  => 0,
                    'total_apartamento' => 0,
                    'total_enfermaria'  => 0,
                ];
            }

            if ($dado->acomodacao_id == 1) {
                $dadosAgrupados[$faixaId]['valor_apartamento'] = $dado->valor;
                $dadosAgrupados[$faixaId]['total_apartamento'] = $dado->valor * $dadosAgrupados[$faixaId]['quantidade'];
            } elseif ($dado->acomodacao_id == 2) {
                $dadosAgrupados[$faixaId]['valor_enfermaria'] = $dado->valor;
                $dadosAgrupados[$faixaId]['total_enfermaria'] = $dado->valor * $dadosAgrupados[$faixaId]['quantidade'];
            }
        }

        return array_values($dadosAgrupados);
    }

    public function criarPDFEmpresarial(Request $request)
    {
        $coparticipacao = (int) $request->input('coparticipacao');
        $odonto         = (int) $request->input('odonto', 1);
        $cidade         = $request->input('tabela_origem');
        $tipo           = $request->input('tipo_documento', 'jpg');
        $faixasInput    = $request->input('faixas')[0];

        $sqlCase  = '';
        $faixasIds = [];
        foreach ($faixasInput as $faixaId => $quantidade) {
            if ($quantidade > 0) {
                $sqlCase  .= " WHEN tabelas.faixa_etaria_id = {$faixaId} THEN {$quantidade}";
                $faixasIds[] = $faixaId;
            }
        }

        if (empty($faixasIds)) {
            return response()->json(['error' => 'Nenhuma faixa etária válida.'], 422);
        }

        $dadosTabela = Tabela::select('tabelas.*')
            ->selectRaw("CASE {$sqlCase} END AS quantidade")
            ->where('tabelas.tabela_origens_id', $cidade)
            ->where('tabelas.plano_id', self::SUPER_SIMPLES_ID)
            ->where('tabelas.administradora_id', self::HAPVIDA_ID)
            ->where('tabelas.coparticipacao', $coparticipacao)
            ->where('tabelas.odonto', $odonto)
            ->whereIn('tabelas.faixa_etaria_id', $faixasIds)
            ->get();

        if ($dadosTabela->isEmpty()) {
            return response()->json(['error' => 'Nenhum dado encontrado.'], 404);
        }

        $dadosAgrupados = $dadosTabela->groupBy('faixa_etaria_id')->map(function ($items, $faixaId) {
            $valorApartamento = 0;
            $valorEnfermaria  = 0;
            $quantidade       = 0;
            foreach ($items as $item) {
                $quantidade = $item->quantidade;
                if ($item->acomodacao_id == 1) $valorApartamento = $item->valor;
                elseif ($item->acomodacao_id == 2) $valorEnfermaria = $item->valor;
            }
            return [
                'faixa_etaria'      => "Faixa {$faixaId}",
                'quantidade'        => $quantidade,
                'valor_apartamento' => number_format($valorApartamento, 2, ',', '.'),
                'valor_enfermaria'  => number_format($valorEnfermaria,  2, ',', '.'),
                'total_apartamento' => number_format($valorApartamento * $quantidade, 2, ',', '.'),
                'total_enfermaria'  => number_format($valorEnfermaria  * $quantidade, 2, ',', '.'),
            ];
        });

        $plano_nome  = Plano::find(self::SUPER_SIMPLES_ID)->nome;
        $cidade_nome = TabelaOrigens::find($cidade)->nome;
        $copart_frase = $coparticipacao == 1 ? ' c/ Copart' : ' s/ Copart';
        $odonto_frase = $odonto         == 1 ? ' c/ Odonto' : ' s/ Odonto';
        $frase = $plano_nome . $copart_frase . $odonto_frase;

        $imagem_user = '';
        $img = auth()->user()->imagem ?? '';
        if ($img) $imagem_user = "storage/{$img}";

        $layout      = auth()->user()->layout_id ?? 1;
        $layout_user = in_array($layout, [1, 2, 3, 4]) ? $layout : 1;

        $view = view("cotacao.modeloempresarial{$layout_user}", [
            'cidade'      => $cidade_nome,
            'label'       => $frase,
            'dadosTabela' => $dadosAgrupados,
            'nome'        => auth()->user()->name,
            'celular'     => auth()->user()->phone,
            'image'       => $imagem_user,
        ])->render();

        $nome_img = 'hapvida-ss-' . date('Ymd_His') . '_' . uniqid();
        $pdfPath  = storage_path('app/temp/temp_hss.pdf');

        $pdf = PDFFile::loadHTML($view)->setPaper('A3', 'portrait');

        if ($tipo === 'pdf') {
            return $pdf->download("{$nome_img}.pdf");
        }

        $pdf->save($pdfPath);
        $imagemPath = storage_path("app/temp/{$nome_img}.png");
        $command = "gs -sDEVICE=pngalpha -r300 -o {$imagemPath} {$pdfPath}";
        exec($command, $output, $status);

        if ($status !== 0 || !file_exists($imagemPath)) {
            return response()->json(['error' => 'Falha ao gerar imagem.'], 500);
        }

        return response()->download($imagemPath)->deleteFileAfterSend(true);
    }

    private function calcularTotais($dadosAgrupados)
    {
        $resultado = [
            'rows'              => [],
            'total_apartamento' => 0,
            'total_enfermaria'  => 0,
        ];

        foreach ($dadosAgrupados as $dado) {
            $resultado['rows'][]          = $dado;
            $resultado['total_apartamento'] += $dado['total_apartamento'];
            $resultado['total_enfermaria']  += $dado['total_enfermaria'];
        }

        return $resultado;
    }
}
