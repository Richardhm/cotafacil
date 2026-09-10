<?php

namespace App\Http\Controllers;

use App\Models\AdministradoraPlano;
use App\Models\EmailAssinatura;
use App\Models\Plano;
use App\Models\RotuloCotacao;
use App\Support\CoparticipacaoCotacao;
use App\Models\Tabela;
use App\Models\TabelaOrigens;
use Barryvdh\DomPDF\Facade\Pdf as PDFFile;
use Illuminate\Http\Request;

class HapvidaSuperSimplesController extends Controller
{
    private const HAPVIDA_ID = 4;
    private const SUPER_SIMPLES_ID = 5;

    /**
     * Planos que podem ser cotados nesta tela (sem limite de vidas).
     * O primeiro é o padrão quando a requisição não manda plano.
     */
    private const PLANOS_PERMITIDOS = [
        5,   // Super Simples
        10,  // Super Simples - Integrado
        11,  // Super Simples - Pleno
        140, // Sindlojas
    ];

    /**
     * Planos que só aparecem para quem tem vínculo em administradora_planos.
     * Os demais de PLANOS_PERMITIDOS continuam visíveis para todo mundo que
     * abre a tela, como sempre foi.
     */
    private const PLANOS_RESTRITOS = [
        140, // Sindlojas
    ];

    /** Planos restritos que a assinatura do usuário logado pode cotar. */
    private function planosRestritosLiberados(): array
    {
        $assinaturaId = EmailAssinatura::where('email', auth()->user()->email)
            ->value('assinatura_id');

        if (!$assinaturaId) {
            return [];
        }

        return AdministradoraPlano::where('assinatura_id', $assinaturaId)
            ->where('administradora_id', self::HAPVIDA_ID)
            ->whereIn('plano_id', self::PLANOS_RESTRITOS)
            ->pluck('plano_id')
            ->unique()
            ->all();
    }

    /** Planos que o usuário logado pode ver e cotar nesta tela. */
    private function planosDisponiveis(): array
    {
        $liberados = $this->planosRestritosLiberados();

        return array_values(array_filter(
            self::PLANOS_PERMITIDOS,
            fn(int $id) => !in_array($id, self::PLANOS_RESTRITOS, true)
                || in_array($id, $liberados, true),
        ));
    }

    /**
     * Plano vindo da requisição, sempre validado contra o que ESTE usuário pode
     * cotar — sem isso daria para cotar um plano restrito mandando o id no POST,
     * mesmo sem ele aparecer no dropdown.
     */
    private function planoSelecionado(Request $request): int
    {
        $plano = (int) $request->input('plano_id', self::SUPER_SIMPLES_ID);

        return in_array($plano, $this->planosDisponiveis(), true)
            ? $plano
            : self::SUPER_SIMPLES_ID;
    }

    /**
     * Variante ambulatorial escolhida no select ("{Plano} - Ambulatorial")?
     * O plano_id continua o mesmo; muda o filtro de acomodação (id 3).
     */
    private function ambulatorialDaRequisicao(Request $request): bool
    {
        return (int) $request->input('ambulatorial', 0) === 1;
    }

    /**
     * Regra dinâmica (mesma do /dashboard): plano ganha a opção Ambulatorial
     * quando existe tabela com acomodacao_id=3 e valor > 0 em alguma cidade.
     */
    private function planosComAmbulatorial(array $planoIds): array
    {
        return Tabela::where('administradora_id', self::HAPVIDA_ID)
            ->whereIn('plano_id', $planoIds)
            ->where('acomodacao_id', 3)
            ->where('valor', '>', 0)
            ->pluck('plano_id')
            ->unique()
            ->values()
            ->all();
    }

    /** UFs que têm tabela para o plano informado (na variante pedida). */
    private function estadosDoPlano(int $planoId, bool $ambulatorial = false)
    {
        $query = Tabela::where('administradora_id', self::HAPVIDA_ID)
            ->where('plano_id', $planoId);

        if ($ambulatorial) {
            $query->where('acomodacao_id', 3)->where('valor', '>', 0);
        } else {
            $query->where('acomodacao_id', '!=', 3);
        }

        $cidadeIds = $query->pluck('tabela_origens_id')->unique();

        return TabelaOrigens::whereIn('id', $cidadeIds)
            ->groupBy('uf')
            ->select('uf')
            ->orderBy('uf')
            ->get();
    }

    public function index()
    {
        $planoPadrao = self::SUPER_SIMPLES_ID;

        $disponiveis = $this->planosDisponiveis();

        $planos = Plano::whereIn('id', $disponiveis)
            ->orderByRaw('FIELD(id, ' . implode(',', $disponiveis) . ')')
            ->get(['id', 'nome']);

        $planosAmbulatoriais = $this->planosComAmbulatorial($disponiveis);

        $estados = $this->estadosDoPlano($planoPadrao);

        $ufpreferencia = auth()->user()->uf_preferencia ?? '';

        return view('hapvida-super-simples.index', compact('estados', 'ufpreferencia', 'planos', 'planosAmbulatoriais'))
            ->with('hapvidaId', self::HAPVIDA_ID)
            ->with('planoSelecionado', $planoPadrao);
    }

    /** UFs disponíveis para o plano — usado quando o usuário troca de plano na tela. */
    public function getEstados(Request $request)
    {
        return response()->json($this->estadosDoPlano(
            $this->planoSelecionado($request),
            $this->ambulatorialDaRequisicao($request)
        ));
    }

    public function getCidades(Request $request)
    {
        $uf = $request->input('uf');

        $query = Tabela::where('administradora_id', self::HAPVIDA_ID)
            ->where('plano_id', $this->planoSelecionado($request));

        if ($this->ambulatorialDaRequisicao($request)) {
            $query->where('acomodacao_id', 3)->where('valor', '>', 0);
        } else {
            $query->where('acomodacao_id', '!=', 3);
        }

        $cidadeIds = $query->pluck('tabela_origens_id')->unique();

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
        $planoId = $this->planoSelecionado($request);
        $ambulatorial = $this->ambulatorialDaRequisicao($request);
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
                ->where('tabelas.plano_id', $planoId)
                ->where('tabelas.administradora_id', self::HAPVIDA_ID)
                ->where('tabelas.coparticipacao', $cenario['copart'])
                ->where('tabelas.odonto', $cenario['odonto'])
                ->when($ambulatorial,
                    fn ($q) => $q->where('tabelas.acomodacao_id', 3)->where('tabelas.valor', '>', 0),
                    fn ($q) => $q->where('tabelas.acomodacao_id', '!=', 3))
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
                    'ambulatorial'      => $ambulatorial ? 1 : 0,
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
            } elseif ($dado->acomodacao_id == 2 || $dado->acomodacao_id == 3) {
                // Ambulatorial (3) ocupa o slot da enfermaria: a variante mostra
                // uma coluna única, e o normal filtra acomodacao_id != 3 antes
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

        // Opções do modal Gerar Documento: Completa mostra as colunas de Preços
        // Unitários, Resumida esconde; Apartamento/Enfermaria filtram as colunas
        $mostrarUnitarios   = $request->input('mostrar_unitarios', 'true')   === 'true' ? 1 : 0;
        $mostrarApartamento = $request->input('mostrar_apartamento', 'true') === 'true' ? 1 : 0;
        $mostrarEnfermaria  = $request->input('mostrar_enfermaria', 'true')  === 'true' ? 1 : 0;
        if (!$mostrarApartamento && !$mostrarEnfermaria) {
            $mostrarApartamento = 1;
            $mostrarEnfermaria  = 1;
        }

        // Variante Ambulatorial: coluna única (usa o slot da enfermaria com
        // rótulo próprio), a escolha de acomodação do modal não se aplica
        $ambulatorial = $this->ambulatorialDaRequisicao($request);
        if ($ambulatorial) {
            $mostrarApartamento = 0;
            $mostrarEnfermaria  = 1;
        }
        $planoId        = $this->planoSelecionado($request);
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
            ->where('tabelas.plano_id', $planoId)
            ->where('tabelas.administradora_id', self::HAPVIDA_ID)
            ->where('tabelas.coparticipacao', $coparticipacao)
            ->where('tabelas.odonto', $odonto)
            ->when($ambulatorial,
                fn ($q) => $q->where('tabelas.acomodacao_id', 3)->where('tabelas.valor', '>', 0),
                fn ($q) => $q->where('tabelas.acomodacao_id', '!=', 3))
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
                elseif ($item->acomodacao_id == 2 || $item->acomodacao_id == 3) $valorEnfermaria = $item->valor;
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

        $plano_nome  = RotuloCotacao::resolver(auth()->user(), 'nome_plano', (int) $planoId, Plano::find($planoId)->nome);
        if ($ambulatorial) {
            $plano_nome .= ' - Ambulatorial';
        }
        $cidade_nome = TabelaOrigens::find($cidade)->nome;
        // Título sem a parte de copart (pedido de 08/09): só plano + odonto
        $odonto_frase = $odonto == 1 ? ' c/ Odonto' : ' s/ Odonto';
        $frase = $plano_nome . $odonto_frase;

        $imagem_user = '';
        $img = auth()->user()->imagem ?? '';
        if ($img && \Illuminate\Support\Facades\Storage::disk('public')->exists($img)) $imagem_user = "storage/{$img}";

        $layout      = auth()->user()->layout_id ?? 1;
        $layout_user = in_array($layout, [1, 2, 3, 4]) ? $layout : 1;

        // Tabelinhas de coparticipação (mesmo bloco do dashboard) — só a copay
        // escolhida; ambulatorial prefere a linha própria da pdf (com fallback)
        $copart = CoparticipacaoCotacao::montar((int) $planoId, (int) $cidade, self::HAPVIDA_ID, ambulatorial: $ambulatorial);

        $view = view("cotacao.modeloempresarial{$layout_user}", [
            'pdf'                   => $copart['pdf'],
            'quantidade_copar'      => $copart['quantidade_copar'],
            'status_excecao'        => $copart['status_excecao'],
            'linha_01'              => $copart['linha_01'],
            'linha_02'              => $copart['linha_02'],
            'copart_com'            => $coparticipacao ? 1 : 0,
            'copart_sem'            => $coparticipacao ? 0 : 1,
            'apenas_valores'        => 0,
            'rotulo_com_copart'     => RotuloCotacao::resolver(auth()->user(), 'com_copart', null, null),
            'rotulo_copart_parcial' => RotuloCotacao::resolver(auth()->user(), 'copart_parcial', null, null),
            'mostrar_unitarios'   => $mostrarUnitarios,
            'mostrar_apartamento' => $mostrarApartamento,
            'mostrar_enfermaria'  => $mostrarEnfermaria,
            'rotulo_enfer'        => $ambulatorial ? 'AMBUL.' : null,
            'plano_titulo'        => $plano_nome, // vira o cabeçalho do bloco IDADE
            'cidade'      => $cidade_nome,
            'label'       => $frase,
            'dadosTabela' => $dadosAgrupados,
            'nome'        => auth()->user()->name,
            'celular'     => auth()->user()->phone,
            'image'       => $imagem_user,
        ])->render();

        $nome_img = 'hapvida-ss-' . date('Ymd_His') . '_' . uniqid();

        // Nome exclusivo por requisição: com temp_hss.pdf fixo, dois usuários gerando ao
        // mesmo tempo sobrescreviam o PDF um do outro no meio da leitura do ghostscript.
        $diretorio = storage_path('app/temp');
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0775, true);
        }
        $pdfPath = $diretorio . DIRECTORY_SEPARATOR . 'hss_' . uniqid('', true) . '.pdf';

        $pdf = PDFFile::loadHTML($view)->setPaper('A3', 'portrait');

        if ($tipo === 'pdf') {
            return $pdf->download("{$nome_img}.pdf");
        }

        $pdf->save($pdfPath);
        $imagemPath = storage_path("app/temp/{$nome_img}.png");
        $command = "gs -sDEVICE=pngalpha -r300 -o {$imagemPath} {$pdfPath}";
        exec($command, $output, $status);

        @unlink($pdfPath);

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
