<?php

namespace App\Http\Controllers;

use App\Models\Humana\HumanaFaixaEtaria;
use App\Models\Humana\HumanaPlano;
use App\Models\Humana\HumanaTabela;
use Barryvdh\DomPDF\Facade\Pdf as PDFFile;
use Illuminate\Http\Request;

/**
 * Cotação da operadora Humana (Teresina-PI). Módulo isolado da Hapvida:
 * consulta apenas as tabelas humana_*. Acesso controlado pelo middleware
 * 'humana' em TODAS as rotas do módulo.
 */
class HumanaController extends Controller
{
    // Rótulo da acomodação muda por contratação (PDF PME chama de
    // Coletiva/Individual o que o PF chama de Enfermaria/Apartamento).
    private const LABELS_ACOMODACAO = [
        'pf'  => ['enfermaria' => 'Enfermaria', 'apartamento' => 'Apartamento', 'nenhuma' => '—'],
        'pme' => ['enfermaria' => 'Coletiva',   'apartamento' => 'Individual',  'nenhuma' => '—'],
    ];

    private const LABELS_COPAY = [
        'completa'      => 'Completa',
        'basica'        => 'Básica',
        'nao_se_aplica' => 'Não se aplica',
    ];

    public function index()
    {
        $planos = HumanaPlano::ativos()->get()->groupBy('contratacao');

        return view('humana.index', [
            'planosPf'  => $planos->get('pf', collect()),
            'planosPme' => $planos->get('pme', collect()),
        ]);
    }

    /**
     * Todas as tabelas de preço da linha escolhida, num JSON só.
     * São no máximo 4 combinações (acomodação × copay) × 10 faixas × 3 valores —
     * o front alterna acomodação/copay no cliente, sem novas requisições.
     */
    public function precos(Request $request)
    {
        $dados = $request->validate([
            'plano_id' => 'required|integer|exists:humana_planos,id',
        ]);

        $plano = HumanaPlano::with(['tabelas.precos'])
            ->where('ativo', true)
            ->findOrFail($dados['plano_id']);

        $labelsAcomodacao = self::LABELS_ACOMODACAO[$plano->contratacao];

        $tabelas = $plano->tabelas->map(function ($tabela) use ($labelsAcomodacao) {
            $precos = [];
            foreach ($tabela->precos as $preco) {
                $precos[$preco->humana_faixa_etaria_id] = [
                    'saude'     => (float) $preco->valor_saude,
                    'essencial' => $preco->valor_combo_essencial !== null ? (float) $preco->valor_combo_essencial : null,
                    'pleno'     => $preco->valor_combo_pleno !== null ? (float) $preco->valor_combo_pleno : null,
                ];
            }

            return [
                'acomodacao'           => $tabela->acomodacao,
                'acomodacao_label'     => $labelsAcomodacao[$tabela->acomodacao],
                'coparticipacao'       => $tabela->coparticipacao,
                'coparticipacao_label' => self::LABELS_COPAY[$tabela->coparticipacao],
                'registro_ans'         => $tabela->registro_ans,
                'vigencia_inicio'      => $tabela->vigencia_inicio?->format('d/m/Y'),
                'vigencia_fim'         => $tabela->vigencia_fim?->format('d/m/Y'),
                'precos'               => $precos,
            ];
        })->values();

        return response()->json([
            'plano' => [
                'id'          => $plano->id,
                'nome'        => $plano->nome,
                'contratacao' => $plano->contratacao,
                'linha'       => $plano->linha,
                'obstetricia' => $plano->obstetricia,
                'segmentacao' => $plano->segmentacao,
                'abrangencia' => $plano->abrangencia,
            ],
            'faixas'  => HumanaFaixaEtaria::orderBy('id')->get(['id', 'nome']),
            'tabelas' => $tabelas,
        ]);
    }

    // Grade de coparticipação das páginas 3 dos PDFs, para o documento gerado.
    // (O JS da tela tem a própria cópia — mudou o PDF da Humana, atualizar os dois.)
    private const COPAY_COMPLETA = [
        'vital' => [
            ['Consulta eletiva', 'R$ 20,00'],
            ['Consulta em hospital (pronto-socorro)', 'R$ 30,00'],
            ['Exames simples', '25% com limitador de R$ 25,00'],
            ['Exames especiais', '25% com limitador de R$ 80,00'],
            ['Terapias — Grupo 1', '25% com limitador de R$ 30,00'],
            ['Terapias — Grupo 2', 'Isento'],
            ['Terapias — Grupo 3', '40% com limitador de R$ 70,00'],
            ['Internação', 'Isento'],
        ],
        'superior' => [
            ['Consulta eletiva', 'R$ 20,00'],
            ['Consulta em hospital (pronto-socorro)', 'R$ 40,00'],
            ['Exames simples', '30% com limitador de R$ 25,00'],
            ['Exames especiais', '30% com limitador de R$ 80,00'],
            ['Terapias — Grupo 1', '30% com limitador de R$ 30,00'],
            ['Terapias — Grupo 2', 'Isento'],
            ['Terapias — Grupo 3', '40% com limitador de R$ 70,00'],
            ['Internação', 'Isento'],
        ],
    ];

    private const COPAY_BASICA = [
        ['Consulta eletiva', 'Isento'],
        ['Consulta em hospital (pronto-socorro)', 'Isento'],
        ['Exames simples', 'Isento'],
        ['Exames especiais', 'Isento'],
        ['Terapias — Grupo 1', 'Isento'],
        ['Terapias — Grupo 2', 'Isento'],
        ['Terapias — Grupo 3', '40% com limitador de R$ 70,00'],
        ['Internação', 'Isento'],
    ];

    // Colunas de produto exibíveis no documento (pedido da usuária: poder
    // escolher UMA, como escolhe acomodação/copay — 'todos' = as 3 juntas)
    private const PRODUTOS = [
        'saude'     => 'Valor Saúde',
        'essencial' => 'Combo Essencial',
        'pleno'     => 'Combo Odonto Pleno',
    ];

    /**
     * Gera o documento da cotação (PDF ou imagem PNG via Ghostscript).
     * Os valores são SEMPRE recalculados do banco — nada vem pronto do cliente.
     * comparativo=1: Copay Completa e Básica lado a lado no MESMO documento.
     */
    public function gerar(Request $request)
    {
        $dados = $request->validate([
            'plano_id'       => 'required|integer|exists:humana_planos,id',
            'acomodacao'     => 'required|in:enfermaria,apartamento,nenhuma',
            'coparticipacao' => 'required|in:completa,basica,nao_se_aplica',
            'faixas'         => 'required|array',
            'faixas.*'       => 'integer|min:0|max:999',
            'tipo_documento' => 'required|in:pdf,jpg',
            'produto'        => 'nullable|in:todos,saude,essencial,pleno',
            'comparativo'    => 'nullable|boolean',
        ]);

        $plano = HumanaPlano::where('ativo', true)->findOrFail($dados['plano_id']);

        $vidas = collect($dados['faixas'])
            ->map(fn ($qtd) => (int) $qtd)
            ->filter(fn ($qtd) => $qtd > 0);
        if ($vidas->isEmpty()) {
            return response()->json(['error' => 'Nenhuma faixa etária com vidas.'], 422);
        }

        $produto     = $dados['produto'] ?? 'todos';
        $comparativo = (bool) ($dados['comparativo'] ?? false);

        $buscarTabela = fn (string $copay) => HumanaTabela::with('precos.faixaEtaria')
            ->where('humana_plano_id', $plano->id)
            ->where('acomodacao', $dados['acomodacao'])
            ->where('coparticipacao', $copay)
            ->firstOrFail();   // combinação inválida = 404, não documento errado

        $grupoCopay = in_array($plano->linha, ['VITAL', 'AMBULATORIAL']) ? 'vital' : 'superior';
        $ajustarGrade = function (array $grade) use ($plano): array {
            if ($plano->linha !== 'AMBULATORIAL') {
                return $grade;
            }
            return array_map(
                fn ($item) => $item[0] === 'Internação' ? ['Internação', 'Não se aplica'] : $item,
                $grade
            );
        };

        // Extrai de uma tabela as linhas (só faixas com vidas) e os totais
        $montar = function (HumanaTabela $tabela) use ($vidas): array {
            $linhas = [];
            $totais = ['saude' => 0, 'essencial' => 0, 'pleno' => 0];
            foreach ($tabela->precos->sortBy('humana_faixa_etaria_id') as $preco) {
                $qtd = $vidas->get($preco->humana_faixa_etaria_id) ?? $vidas->get((string) $preco->humana_faixa_etaria_id);
                if (!$qtd) {
                    continue;
                }
                $linha = [
                    'faixa'     => $preco->faixaEtaria->nome,
                    'qtd'       => $qtd,
                    'saude'     => (float) $preco->valor_saude,
                    'essencial' => $preco->valor_combo_essencial !== null ? (float) $preco->valor_combo_essencial : null,
                    'pleno'     => $preco->valor_combo_pleno !== null ? (float) $preco->valor_combo_pleno : null,
                ];
                $totais['saude']     += $linha['saude'] * $qtd;
                $totais['essencial'] += ($linha['essencial'] ?? 0) * $qtd;
                $totais['pleno']     += ($linha['pleno'] ?? 0) * $qtd;
                $linhas[] = $linha;
            }
            return [$linhas, $totais];
        };

        if ($comparativo) {
            // Completa e Básica lado a lado — só existe onde há as duas
            $tabelaCompleta = $buscarTabela('completa');
            $tabelaBasica   = $buscarTabela('basica');
            [$linhasCompleta, $totaisCompleta] = $montar($tabelaCompleta);
            [, $totaisBasica] = $montar($tabelaBasica);
            $precosBasica = [];
            foreach ($tabelaBasica->precos as $preco) {
                $precosBasica[$preco->faixaEtaria->nome] = [
                    'saude'     => (float) $preco->valor_saude,
                    'essencial' => $preco->valor_combo_essencial !== null ? (float) $preco->valor_combo_essencial : null,
                    'pleno'     => $preco->valor_combo_pleno !== null ? (float) $preco->valor_combo_pleno : null,
                ];
            }

            $produtos = $produto === 'todos'
                ? self::PRODUTOS
                : [$produto => self::PRODUTOS[$produto]];

            $view = view('humana.pdf-comparativo', [
                'plano'           => $plano,
                'tabela'          => $tabelaCompleta,
                'acomodacaoLabel' => self::LABELS_ACOMODACAO[$plano->contratacao][$dados['acomodacao']],
                'produtos'        => $produtos,
                'linhas'          => $linhasCompleta,
                'precosBasica'    => $precosBasica,
                'totaisCompleta'  => $totaisCompleta,
                'totaisBasica'    => $totaisBasica,
                'totalVidas'      => $vidas->sum(),
                'gradeCompleta'   => $ajustarGrade(self::COPAY_COMPLETA[$grupoCopay]),
                'gradeBasica'     => $ajustarGrade(self::COPAY_BASICA),
                'corretor'        => ['nome' => auth()->user()->name, 'celular' => auth()->user()->phone],
                'geradoEm'        => now()->format('d/m/Y'),
            ])->render();

            // Com os 3 produtos são 6 colunas de valores: paisagem respira melhor
            $orientacao  = $produto === 'todos' ? 'landscape' : 'portrait';
            $nomeArquivo = 'humana-comparativo-' . date('Ymd_His') . '_' . uniqid();
            $pdf = PDFFile::loadHTML($view)->setPaper('a4', $orientacao);
        } else {
            $tabela = $buscarTabela($dados['coparticipacao']);
            [$linhas, $totais] = $montar($tabela);

            $temCombos = $tabela->coparticipacao !== 'nao_se_aplica';
            if (!$temCombos) {
                $produtos = ['saude' => self::PRODUTOS['saude']];   // REFERÊNCIA só tem saúde
            } elseif ($produto === 'todos') {
                $produtos = self::PRODUTOS;
            } else {
                $produtos = [$produto => self::PRODUTOS[$produto]];
            }

            $gradeCopay = match ($tabela->coparticipacao) {
                'completa' => $ajustarGrade(self::COPAY_COMPLETA[$grupoCopay]),
                'basica'   => $ajustarGrade(self::COPAY_BASICA),
                default    => null,
            };

            $view = view('humana.pdf', [
                'plano'           => $plano,
                'tabela'          => $tabela,
                'acomodacaoLabel' => self::LABELS_ACOMODACAO[$plano->contratacao][$tabela->acomodacao],
                'copayLabel'      => self::LABELS_COPAY[$tabela->coparticipacao],
                'produtos'        => $produtos,
                'linhas'          => $linhas,
                'totais'          => $totais,
                'totalVidas'      => $vidas->sum(),
                'temCombos'       => $temCombos,
                'gradeCopay'      => $gradeCopay,
                'corretor'        => ['nome' => auth()->user()->name, 'celular' => auth()->user()->phone],
                'geradoEm'        => now()->format('d/m/Y'),
            ])->render();

            $nomeArquivo = 'humana-' . date('Ymd_His') . '_' . uniqid();
            $pdf = PDFFile::loadHTML($view)->setPaper('a4', 'portrait');
        }

        if ($dados['tipo_documento'] === 'pdf') {
            return $pdf->download("{$nomeArquivo}.pdf");
        }

        // Nome exclusivo por requisição (nome fixo já causou colisão em produção)
        $diretorio = storage_path('app/temp');
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0775, true);
        }
        $pdfPath = $diretorio . DIRECTORY_SEPARATOR . 'humana_' . uniqid('', true) . '.pdf';
        $pdf->save($pdfPath);

        $imagemPath = storage_path("app/temp/{$nomeArquivo}.png");
        $command = "gs -sDEVICE=pngalpha -r300 -o {$imagemPath} {$pdfPath}";
        exec($command, $output, $status);

        @unlink($pdfPath);

        if ($status !== 0 || !file_exists($imagemPath)) {
            return response()->json(['error' => 'Falha ao gerar imagem.'], 500);
        }

        return response()->download($imagemPath)->deleteFileAfterSend(true);
    }
}
