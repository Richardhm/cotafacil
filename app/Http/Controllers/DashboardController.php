<?php

namespace App\Http\Controllers;

use App\Models\Administradora;
use App\Models\AdministradoraPlano;
use App\Models\Assinatura;
use App\Models\Carencia;
use App\Models\Desconto;
use App\Models\Codigo;
use App\Models\CodigoAmbulatorial;
use App\Models\EmailAssinatura;
use App\Models\Layout;
use App\Models\PdfExcecao;
use App\Models\Plano;
use App\Models\RotuloCotacao;
use App\Support\CoparticipacaoCotacao;
use App\Models\Tabela;
use App\Models\Pdf;
use App\Models\TabelaOrigens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDFFile;

class DashboardController extends Controller
{
    /**
     * Caminho exclusivo para o PDF intermediário da geração de imagem.
     *
     * Antes era sempre storage/app/temp/temp.pdf — nome fixo para todo mundo. Com dois
     * usuários gerando cotação ao mesmo tempo, um salvava por cima do outro enquanto o
     * ghostscript ainda estava lendo: ou saía a imagem do outro, ou o gs terminava com
     * código 0 sem escrever nada e a requisição virava 500 (o spinner ficava eterno).
     */
    private function novoPdfTemporario(): string
    {
        $diretorio = storage_path('app/temp');

        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0775, true);
        }

        return $diretorio . DIRECTORY_SEPARATOR . 'pdf_' . uniqid('', true) . '.pdf';
    }

    public function tabelaCompletaAmbulatorial()
    {
        $layout = auth()->user()->layout_id;
        $layout_user = in_array($layout, [1, 2, 3, 4]) ? $layout : 1;
        $viewName   = "cotacao.modelotabelaambulatorial".$layout_user;
        $cidade     = request()->cidade;
        $plano      = request()->plano;
        $operadora  = request()->operadora;
        $odonto     = request()->odonto;
        $plano_nome = RotuloCotacao::resolver(auth()->user(), 'nome_plano', (int) $plano, Plano::find($plano)->nome);
        $odonto_frase = $odonto ? "Com Odonto" : "Sem Odonto";
        $frase = "Ambulatorial/".$odonto_frase;

        $sql = "";
        $chaves = [];

        foreach(request()->faixas[0] as $k => $v) {
            if($v != null AND $v != 0) {
                $sql .= " WHEN tabelas.faixa_etaria_id = {$k} THEN {$v} ";
                $chaves[] = $k;
            }
        }

        $keys = implode(",",$chaves);
        $cidade_nome = TabelaOrigens::find($cidade)->nome;
        $dados = Tabela::select('tabelas.*')
            ->selectRaw("CASE $sql END AS quantidade")
            ->join('faixa_etarias', 'faixa_etarias.id', '=', 'tabelas.faixa_etaria_id')
            ->where('tabelas.tabela_origens_id', $cidade)
            ->where('tabelas.plano_id', $plano)
            ->where('tabelas.administradora_id', $operadora)
            ->where("tabelas.odonto",$odonto)
            ->where("acomodacao_id","=",3)
            ->whereIn('tabelas.faixa_etaria_id', explode(',', $keys))
            ->orderBy('tabelas.faixa_etaria_id')
            ->get();

        $imagem_user = "";
        $image = auth()->user()->image;
        if($image != "") {
            $imagem_user = auth()->user()->image;
        }
        $nome = auth()->user()->name;
        $celular = auth()->user()->phone;

        $view = \Illuminate\Support\Facades\View::make($viewName,[
                'apelido_plano' => Plano::find($plano)?->apelido,
                'rotulo_com_copart' => RotuloCotacao::resolver(auth()->user(), 'com_copart', null, null),
                'rotulo_copart_parcial' => RotuloCotacao::resolver(auth()->user(), 'copart_parcial', null, null),
            "dados" => $dados,
            "image" => $imagem_user,
            "nome" => $nome,
            "celular" => $celular,
            "cidade_nome" => $cidade_nome,
            "frase" => $frase,
            // Tabelinhas de coparticipação (mesmo bloco do dashboard). A tabela
            // completa mostra as duas colunas -> as duas tabelinhas (flags do blade)
            'pdf'              => ($copart = CoparticipacaoCotacao::montar((int) $plano, (int) $cidade, (int) $operadora))['pdf'],
            'quantidade_copar' => $copart['quantidade_copar'],
            'status_excecao'   => $copart['status_excecao'],
            'linha_01'         => $copart['linha_01'],
            'linha_02'         => $copart['linha_02'],
            'apenas_valores'   => 0,
        ]);

        $pdfPath = $this->novoPdfTemporario();
        $pdf = PDFFile::loadHTML($view)->setPaper('A3', 'portrait');
        $pdf->save($pdfPath);

        $nomeArquivo = 'tabela-ambulatorial-' . date('dmY-His') . '-' . uniqid() . '.png';

        $imagemPath = storage_path("app/temp/{$nomeArquivo}");

        if (file_exists($imagemPath)) {
            unlink($imagemPath);
        }

        $command = "gs -sDEVICE=pngalpha -r300 -o {$imagemPath} {$pdfPath}";  // -r150 é a resolução, pode ser ajustada
        exec($command, $output, $status);
        @unlink($pdfPath);

        if ($status !== 0 || !file_exists($imagemPath)) {
            return response()->json(['error' => 'Falha ao gerar a imagem.'], 500);
        }

        return response()->download($imagemPath)->deleteFileAfterSend(true);






    }



    public function tabelaCompleta()
    {
        $layout = auth()->user()->layout_id;
        $layout_user = in_array($layout, [1, 2, 3, 4]) ? $layout : 1;


        $viewName   = "cotacao.modelotabela".$layout_user;

        $cidade     = request()->cidade;
        $plano      = request()->plano;
        $operadora  = request()->operadora;
        $odonto     = request()->odonto;

        $plano_nome = RotuloCotacao::resolver(auth()->user(), 'nome_plano', (int) $plano, Plano::find($plano)->nome);
        $odonto_frase = $odonto ? "Com Odonto" : "Sem Odonto";
        $frase = $plano_nome."/".$odonto_frase;

        $sql = "";
        $chaves = [];

        foreach(request()->faixas[0] as $k => $v) {
            if($v != null AND $v != 0) {
                $sql .= " WHEN tabelas.faixa_etaria_id = {$k} THEN {$v} ";
                $chaves[] = $k;
            }
        }

        $keys = implode(",",$chaves);
        $cidade_nome = TabelaOrigens::find($cidade)->nome;
        $dados = Tabela::select('tabelas.*')
            ->selectRaw("CASE $sql END AS quantidade")
            ->join('faixa_etarias', 'faixa_etarias.id', '=', 'tabelas.faixa_etaria_id')
            ->where('tabelas.tabela_origens_id', $cidade)
            ->where('tabelas.plano_id', $plano)
            ->where('tabelas.administradora_id', $operadora)
            ->where("tabelas.odonto",$odonto)
            ->where("acomodacao_id","!=",3)
            ->whereIn('tabelas.faixa_etaria_id', explode(',', $keys))
            ->orderBy('tabelas.faixa_etaria_id')
            ->get();

        $imagem_user = "";
        $image = auth()->user()->image;
        if($image != "") {
            $imagem_user = auth()->user()->image;
        }
        $nome = auth()->user()->name;
        $celular = auth()->user()->phone;

        $view = \Illuminate\Support\Facades\View::make($viewName,[
                'apelido_plano' => Plano::find($plano)?->apelido,
                'rotulo_com_copart' => RotuloCotacao::resolver(auth()->user(), 'com_copart', null, null),
                'rotulo_copart_parcial' => RotuloCotacao::resolver(auth()->user(), 'copart_parcial', null, null),
            "dados" => $dados,
            "image" => $imagem_user,
            "nome" => $nome,
            "celular" => $celular,
            "cidade_nome" => $cidade_nome,
            "frase" => $frase,
            // Tabelinhas de coparticipação (mesmo bloco do dashboard). A tabela
            // completa mostra as duas colunas -> as duas tabelinhas (flags do blade)
            'pdf'              => ($copart = CoparticipacaoCotacao::montar((int) $plano, (int) $cidade, (int) $operadora))['pdf'],
            'quantidade_copar' => $copart['quantidade_copar'],
            'status_excecao'   => $copart['status_excecao'],
            'linha_01'         => $copart['linha_01'],
            'linha_02'         => $copart['linha_02'],
            'apenas_valores'   => 0,
        ]);

        $pdfPath = $this->novoPdfTemporario();
        $pdf = PDFFile::loadHTML($view)->setPaper('A3', 'portrait');
        $pdf->save($pdfPath);

        $nomeArquivo = 'tabela-' . date('dmY-His') . '-' . uniqid() . '.png';


        $imagemPath = storage_path("app/temp/{$nomeArquivo}");

        if (file_exists($imagemPath)) {
            unlink($imagemPath);
        }

        $command = "gs -sDEVICE=pngalpha -r300 -o {$imagemPath} {$pdfPath}";  // -r150 é a resolução, pode ser ajustada
        exec($command, $output, $status);
        @unlink($pdfPath);

        if ($status !== 0 || !file_exists($imagemPath)) {
            return response()->json(['error' => 'Falha ao gerar a imagem.'], 500);
        }

        return response()->download($imagemPath)->deleteFileAfterSend(true);
    }






    public function index()
    {

        $user = auth()->user(); // Usuário logado
        // Busca na tabela emails_assinatura
        $emailAssinatura = EmailAssinatura::where('email', $user->email)->first();

        $assinaturaId = $emailAssinatura?->assinatura_id; // Usando safe operator para evitar erro se não encontrar


        // Buscar só vínculos que pertencem à assinatura do usuário
        $vinculos = AdministradoraPlano::with(['administradora', 'plano', 'cidade'])
            ->where('assinatura_id', $assinaturaId)
            ->get();

        // Pegar administradoras e planos dos vínculos
        $administradoras = $vinculos->pluck('administradora')->unique('id')->values();

        $planos = $vinculos->pluck('plano')->unique('id')->values();
        // Buscar cidades pela assinatura
        //$cidades = $user->assinaturas->tabelasOrigens ?? collect();
        $cidades = $vinculos->pluck('cidade')->unique('id')->values();


        $estados = $vinculos->pluck('cidade')->unique('uf')->sortBy('uf')->values();






        return view('dashboard',[
            'cidades' => $cidades,
            'administradoras' => $administradoras,
            'planos' => $planos,
            'estados' => $estados,
            'uf_preferencia' => $user->uf_preferencia
        ]);
    }

    public function filtrarAdministradora(Request $request)
    {
        $cidade = $request->cidade;

//        $administradora_id = DB::table('tabelas')
//            ->select('administradora_id')
//            ->where('tabela_origens_id', $cidade)
//            ->groupBy('administradora_id')
//            ->get();

        $administradoraIds = DB::table('tabelas')
            ->select('administradora_id')
            ->where('tabela_origens_id', $cidade)
            ->where('administradora_id',"!=",3)
            ->groupBy('administradora_id')
            ->pluck('administradora_id');
        $operadoras = Administradora::whereIn('id', $administradoraIds)
            //->where('cidade', $cidade)
            ->get();


        //$operadoras = Administradora::where('cidade', $cidade)->get();
        return response()->json($operadoras);
    }




    public function getCidadesDeOrigem(Request $request)
    {
        $uf = $request->input('uf'); // Pode ser 'id' se for id do estado

        $cidades = \DB::table('tabela_origens')
            ->where('uf', $uf)
            ->select('id', 'nome')
            ->orderBy('nome')
            ->get();

        return response()->json($cidades);
    }


    public function buscar_planos(Request $request)
    {
        $administradora_id = $request->input('administradora_id');
        $tabela_origens_id = $request->input('tabela_origens_id');

        $assinaturaId = \App\Models\EmailAssinatura::where('email', auth()->user()->email)->first()->assinatura_id;

        // Buscar todos os planos (com grupo ou sem grupo)
        $grupos = DB::table('planos')
            ->leftJoin('plano_group', 'planos.plano_group_id', '=', 'plano_group.id')
            ->join('administradora_planos', 'planos.id', '=', 'administradora_planos.plano_id')
            ->where('administradora_planos.administradora_id', $administradora_id)
            ->where('administradora_planos.tabela_origens_id', $tabela_origens_id)
            ->where('administradora_planos.assinatura_id', $assinaturaId)
            ->select(
                'planos.id as plano_id',
                'planos.nome as plano_nome',
                'plano_group.id as grupo_id',
                'plano_group.nome as grupo_nome'
            )
            ->orderBy('plano_group.nome') // Ordena os grupos
            ->orderBy('planos.nome') // Ordena os planos dentro de cada grupo
            ->get();

        // Deduplicar planos para evitar repetição
        $grupos = $grupos->unique(function ($item) {
            return $item->plano_id . '-' . $item->grupo_id;
        });

        // Separar os planos por grupo (e descartar grupos vazios "")
        $planosPorGrupo = $grupos->filter(function ($plano) {
            return $plano->grupo_id !== null; // Apenas planos com grupo
        })->groupBy('grupo_nome')->map(function ($grupo) {
            return $grupo->map(function ($plano) {
                return [
                    'id' => $plano->plano_id,
                    'nome' => $plano->plano_nome,
                ];
            });
        });

        // Filtrar planos sem grupo
        $planosSemGrupo = $grupos->filter(function ($plano) {
            return $plano->grupo_id === null; // Planos sem grupo
        })->map(function ($plano) {
            return [
                'id' => $plano->plano_id,
                'nome' => $plano->plano_nome,
            ];
        });

        // Regra dinâmica do Ambulatorial: TODO plano desta operadora+cidade+assinatura
        // que tenha valor ambulatorial cadastrado (acomodacao_id=3, valor>0) ganha a
        // opção — não só o Individual. Caso Porto Alegre (63): Individual E Super
        // Simples têm tabela ambulatorial, então os dois aparecem.
        $planosAmbulatoriais = DB::table('tabelas')
            ->join('planos', 'planos.id', '=', 'tabelas.plano_id')
            ->join('administradora_planos', function ($join) use ($administradora_id, $tabela_origens_id, $assinaturaId) {
                $join->on('tabelas.plano_id', '=', 'administradora_planos.plano_id')
                    ->where('administradora_planos.administradora_id', $administradora_id)
                    ->where('administradora_planos.tabela_origens_id', $tabela_origens_id)
                    ->where('administradora_planos.assinatura_id', $assinaturaId);
            })
            ->where('tabelas.administradora_id', $administradora_id)
            ->where('tabelas.tabela_origens_id', $tabela_origens_id)
            ->where('tabelas.acomodacao_id', 3)
            ->where('tabelas.valor', '>', 0)
            ->select('planos.id', 'planos.nome')
            ->distinct()
            ->orderBy('planos.nome')
            ->get();

        return response()->json([
            'planos_por_grupo'      => $planosPorGrupo,
            'planos_sem_grupo'      => $planosSemGrupo,
            // Compat: telas antigas leem tem_ambulatorial/plano_ambulatorial_id (1 só)
            'tem_ambulatorial'      => $planosAmbulatoriais->isNotEmpty(),
            'plano_ambulatorial_id' => optional($planosAmbulatoriais->first())->id,
            'planos_ambulatoriais'  => $planosAmbulatoriais,
        ]);
    }







    public function buscar_planos_2(Request $request)
    {


        $assinaturaId = \App\Models\EmailAssinatura::where('email', auth()->user()->email)->first()->assinatura_id;



        $administradora_id = $request->input('administradora_id');
        $tabela_origens_id = $request->input('tabela_origens_id');


        $planos = DB::table('administradora_planos')
            ->where('administradora_id', $administradora_id)
            ->where('tabela_origens_id', $tabela_origens_id)
            ->where('assinatura_id',$assinaturaId)
            ->pluck('plano_id');



        return response()->json(['planos' => $planos]);
    }

    public function orcamento(Request $request)
    {
        $ambulatorial = $request->ambulatorial;
        $sql = "";
        $chaves = [];
        foreach(request()->faixas[0] as $k => $v) {
            if($v != null AND $v != 0) {
                $sql .= " WHEN tabelas.faixa_etaria_id = {$k} THEN {$v} ";
                $chaves[] = $k;
            }
        }

        $keys = implode(",",$chaves);
        $cidade = request()->tabela_origem;
        $plano = request()->plano;
        $operadora = request()->operadora;
        $imagem_operadora = Administradora::find($operadora)->logo;
        $plano_nome = RotuloCotacao::resolver(auth()->user(), 'nome_plano', (int) $plano, Plano::find($plano)->nome);
        $imagem_plano = Administradora::find($operadora)->logo;
        $cidade_nome = TabelaOrigens::find($cidade)->nome;


        if($ambulatorial == 0) {
            $dados = Tabela::select('tabelas.*')
                ->selectRaw("CASE $sql END AS quantidade")
                ->join('faixa_etarias', 'faixa_etarias.id', '=', 'tabelas.faixa_etaria_id')
                ->where('tabelas.tabela_origens_id', $cidade)
                ->where('tabelas.plano_id', $plano)
                ->where('tabelas.administradora_id', $operadora)
                //->where('acomodacao_id',"!=",3)
                ->whereIn('tabelas.faixa_etaria_id', explode(',', $keys))
                ->get();
            $desconto = Desconto::where("tabela_origens_id",$cidade)->where("plano_id",$plano)->where("administradora_id",$operadora)->count();
            $status_desconto = 0;
            if($desconto == 1) {
                $status_desconto = 1;
            }







            $status = $dados->contains('odonto', 0);
            $status_odonto = $dados->contains('odonto',1);
            return view("cotacao.cotacao2",[
                "dados" => $dados,
                "status_odonto" => $status_odonto,
                "operadora" => $imagem_operadora,
                "plano_nome" => $plano_nome,
                "cidade_nome" => $cidade_nome,
                "imagem_plano" => $imagem_plano,
                "status" => $status,
                "status_desconto" => $status_desconto
            ]);

        } else {
            $dados = Tabela::select('tabelas.*')
                ->selectRaw("CASE $sql END AS quantidade")
                ->join('faixa_etarias', 'faixa_etarias.id', '=', 'tabelas.faixa_etaria_id')
                ->where('tabelas.tabela_origens_id', $cidade)
                ->where('tabelas.plano_id', $plano)
                ->where('tabelas.administradora_id', $operadora)
                ->where('acomodacao_id',"=",3)
                ->whereIn('tabelas.faixa_etaria_id', explode(',', $keys))
                ->get();
            //return $dados;
            $status = $dados->contains('odonto', 0);
            // Editado à mão em produção (resgatado no deploy de 25/08): a prévia
            // ambulatorial do servidor usa status_odonto no blade divergente.
            $status_odonto = $dados->contains('odonto', 1);

            $desconto = Desconto::where("tabela_origens_id",$cidade)->where("plano_id",$plano)->where("administradora_id",$operadora)->count();
            $status_desconto = 0;
            if($desconto == 1) {
                $status_desconto = 1;
            }

            return view("cotacao.cotacao-ambulatorial",[
                "dados" => $dados,
                "status_odonto" => $status_odonto,
                "operadora" => $imagem_operadora,
                "plano_nome" => $plano_nome,
                "cidade_nome" => $cidade_nome,
                "imagem_plano" => $imagem_plano,
                "status" => $status,
                "status_desconto" => $status_desconto
            ]);
        }
    }

    public function criarPDF()
    {




        $com_coparticipacao  = request()->comcoparticipacao  == "true" ? 1 : 0;
        $sem_coparticipacao  = request()->semcoparticipacao  == "true" ? 1 : 0;
        //$status_desconto    = request()->status_desconto    == "true" ? 1 : 0;
        $apenasvalores       = request()->apenasvalores      == "true" ? 1 : 0;
        $tipo_documento      = request()->tipo_documento;
        $mostrar_apartamento = request()->input('mostrar_apartamento', 'true') == "true" ? 1 : 0;
        $mostrar_enfermaria  = request()->input('mostrar_enfermaria',  'true') == "true" ? 1 : 0;

        $ambulatorial = request()->ambulatorial;
        $cidade = request()->tabela_origem;
        $plano = request()->plano;
        $operadora = request()->operadora;
        $administradora = request()->operadora;
        $odonto = request()->odonto;

        $sql = "";
        $chaves = [];
        $linhas = 0;
        $somar_linhas = 0;
        foreach(request()->faixas[0] as $k => $v) {
            if($v != null AND $v != 0) {
                $sql .= " WHEN tabelas.faixa_etaria_id = {$k} THEN {$v} ";
                $chaves[] = $k;
                $somar_linhas += (int) $v;
            }
        }


        $linhas = count($chaves);
        $cidade_nome = TabelaOrigens::find($cidade)->nome;
        $plano_nome = RotuloCotacao::resolver(auth()->user(), 'nome_plano', (int) $plano, Plano::find($plano)->nome);
        $linha_01 = "";
        $linha_02 = "";

        $cidade_uf = TabelaOrigens::find($cidade)->uf;
        $status_excecao = false;

        $admin_nome = Administradora::find($operadora)->nome;
        $odonto_frase = $odonto == 1 ? " c/ Odonto" : " s/ Odonto";
        $frase = $plano_nome.$odonto_frase;
        $keys = implode(",",$chaves);
        $imagem_user = "";
        $image = auth()->user()->imagem;
        if($image != "" && \Illuminate\Support\Facades\Storage::disk('public')->exists($image)) {
            $imagem_user = "storage/".auth()->user()->imagem;
        }

        $nome = auth()->user()->name;
        $celular = auth()->user()->phone;
        $corretora = auth()->user()->corretora_id;
        $status_carencia = request()->status_carencia == "true" ? 1 : 0;
        $status_desconto = request()->status_desconto == "true" ? 1 : 0;
        if($ambulatorial == 0) {
            $dadosPorPagina = 15;
            $dados = Tabela::select('tabelas.*')
                ->selectRaw("CASE $sql END AS quantidade")
                ->join('faixa_etarias', 'faixa_etarias.id', '=', 'tabelas.faixa_etaria_id')
                ->where('tabelas.tabela_origens_id', $cidade)
                ->where('tabelas.plano_id', $plano)
                ->where('tabelas.administradora_id', $operadora)
                ->where("tabelas.odonto",$odonto)
                ->where("acomodacao_id","!=",3)
                ->whereIn('tabelas.faixa_etaria_id', explode(',', $keys))
                ->get();


            $codigo = Codigo::where("tabela_origens_id",$cidade)
                ->where("plano_id",$plano)
                ->where("administradora_id",$operadora)
                ->where("odonto",$odonto)->first();



            $valor_desconto = 0;
            $texto_desconto = '';
            if($status_desconto) {
                $desconto = Desconto::where('plano_id', $plano)->where('tabela_origens_id', $cidade)->where('administradora_id',$operadora)->first();
                if($desconto) {
                    $valor_desconto  = $desconto->valor;
                    $texto_desconto  = $desconto->texto_desconto ?? ('Des. ' . (int)$desconto->valor . '% 3/meses');
                }

            }

//            $desconto = Desconto::where('plano_id', $plano)
//                ->where('tabela_origens_id', $cidade)
//                ->first();
//
//            $valor_desconto = "";
//            $status_desconto = 0;
//            if($desconto) {
//                $valor_desconto = $desconto->valor;
//                $status_desconto = 1;
//            }

            $layout = auth()->user()->layout_id;
            $layout_user = in_array($layout, [1, 2, 3, 4]) ? $layout : 1;
            //$layout_folder = auth()->user()->isFolder() ?: '';

            $assinatura = auth()->user()->assinaturas()->first()->id;

            $excecaoFolder = DB::table('assinatura_folders')->where([
                ['assinatura_id', $assinatura],
                ['tabela_origens_id', $cidade]
            ])->first();
            if ($excecaoFolder) {
                $layout_folder = $excecaoFolder->folder;
            } else {

                $layout_folder = auth()->user()->isFolder() ?: '';

            }
            $quantidade_cop = 0;
            $viewName = "cotacao.modelo{$layout_user}";
            if($apenasvalores == 0) {
                $pdf_excecao = PdfExcecao::where("plano_id",$plano)->where("tabela_origens_id",$cidade)->count();
                if($pdf_excecao == 1) {
                    $status_excecao = true;
                    $pdf_copar = PdfExcecao::where("plano_id",$plano)->where("tabela_origens_id",$cidade)->first();
                    $quantidade_cop = 1;
                } else {
                    $hasFullMatch = Pdf::where('plano_id', $plano)
                        ->where('tabela_origens_id', $cidade)
                        ->where('administradora_id', $administradora)
                        ->exists();

                    if ($hasFullMatch) {
                        // Caso plano_id, tabela_origens_id e administradora_id correspondam
                        $quantidade_cop = 1;
                        $pdf_copar = Pdf::where('plano_id', $plano)
                            ->where('tabela_origens_id', $cidade)
                            ->where('administradora_id', $administradora)
                            ->first();

                    } else {
                        // Verifica se existe plano_id e tabela_origens_id
                        $hasTabelaOrigens = Pdf::where('plano_id', $plano)
                            ->where('tabela_origens_id', $cidade)
                            ->exists();

                        if ($hasTabelaOrigens) {
                            // Caso plano_id e tabela_origens_id correspondam
                            $quantidade_cop = 1;
                            $pdf_copar = Pdf::where('plano_id', $plano)
                                ->where('tabela_origens_id', $cidade)
                                ->first();

                        } else {
                            // Caso apenas plano_id corresponda
                            $pdf_copar = Pdf::where('plano_id', $plano)->first();

                        }
                    }

                    // Processa os dados caso existam valores na coluna linha02
                    if (isset($pdf_copar->linha02) && $pdf_copar->linha02) {
                        $quantidade_cop = 1;
                        $itens = explode('|', $pdf_copar->linha02);
                        $itensFormatados = array_map(function ($item) {
                            return trim($item); // Remove espaços extras
                        }, $itens);

                        $linha_01 = $itensFormatados[0];
                        $linha_02 = $itensFormatados[1];
                    }

                }


                $carencia = Carencia::where("plano_id",$plano)->where("tabela_origens_id",$cidade)->get();


                $quantidade_carencia = Carencia::where("plano_id",$plano)->where("tabela_origens_id",$cidade)->count();



                $view = \Illuminate\Support\Facades\View::make($viewName,[
                'apelido_plano' => Plano::find($plano)?->apelido,
                'rotulo_com_copart' => RotuloCotacao::resolver(auth()->user(), 'com_copart', null, null),
                'rotulo_copart_parcial' => RotuloCotacao::resolver(auth()->user(), 'copart_parcial', null, null),
                    'com_coparticipacao' => $com_coparticipacao,
                    'sem_coparticipacao' => $sem_coparticipacao,
                    'assinatura' => $assinatura,
                    'apenas_valores' => $apenasvalores,
                    'folder' => $layout_folder,
                    'linha_01' => $linha_01,
                    'codigo' => $codigo,
                    'quantidade_carencia' => $quantidade_carencia,
                    'quantidade_copar' => $quantidade_cop,
                    //'carencia' => 0,
                    'linha_02' => $linha_02,
                    'carencia_texto' => $carencia,
                    'valor_desconto' => $valor_desconto,
                    'texto_desconto' => $texto_desconto,
                    'desconto' => $status_desconto,
                    //'carencias' => $carencias,
                    'image' => $imagem_user,
                    'dados' => $dados,
                    'pdf' => $pdf_copar,
                    'nome' => $nome,
                    'cidade' => $cidade_nome,
                    'plano_nome' => $plano_nome,
                    'odonto_frase' => $odonto_frase,
                    'administradora' => $admin_nome,
                    'frase' => $frase,
                    'carencia' => $status_carencia,
                    'status_desconto' => $status_desconto,
                    'odonto' => $odonto,
                    'celular' => $celular,
                    'status_excecao' => $status_excecao,
                    'linhas' => $linhas,
                    'corretora' => $corretora,
                    'mostrar_apartamento' => $mostrar_apartamento,
                    'mostrar_enfermaria' => $mostrar_enfermaria,
                ]);
            } else {
                //cabecalhos

                $cabecalho = auth()->user()->layout_id;
                $cabecalho_user = in_array($cabecalho, [1, 2, 3, 4]) ? $cabecalho : 1;
                $cabecalhoName = "cotacao.cabecalho{$cabecalho_user}";

                $layout_folder = auth()->user()->isFolder() ?: '';



                $view = \Illuminate\Support\Facades\View::make($cabecalhoName,[
                'apelido_plano' => Plano::find($plano)?->apelido,
                'rotulo_com_copart' => RotuloCotacao::resolver(auth()->user(), 'com_copart', null, null),
                'rotulo_copart_parcial' => RotuloCotacao::resolver(auth()->user(), 'copart_parcial', null, null),
                    'com_coparticipacao' => $com_coparticipacao,
                    'sem_coparticipacao' => $sem_coparticipacao,
                    'apenas_valores' => $apenasvalores,
                    'cabecalho' => $cabecalho,
                    'folder' => $layout_folder,
                    //'carencias' => $carencias,
                    'dados' => $dados,
                    //'pdf' => $pdf_copar,
                    'linha_01' => $linha_01,
                    'linha_02' => $linha_02,
                    'nome' => $nome,
                    'cidade' => $cidade_nome,
                    'plano_nome' => $plano_nome,
                    'odonto_frase' => $odonto_frase,
                    'administradora' => $admin_nome,
                    'frase' => $frase,
                    'status_desconto' => $status_desconto,
                    'odonto' => $odonto,
                ]);
            }

            $nome_img = "orcamento_". date('d') . "_" . date('m') . "_" . date("Y") . "_" . date('H') . "_" . date("i") . "_" . date("s")."_" . uniqid();
            $altura = match (true) {
                $somar_linhas === 1 => 350,
                $somar_linhas === 2 => 380,
                $somar_linhas === 3 => 420,
                $somar_linhas >= 4 && $somar_linhas <= 5 => 500,
                $somar_linhas >= 6 && $somar_linhas <= 7 => 580,
                default => 580,
            };

            if($tipo_documento == "pdf") {

                if ($apenasvalores == 1) {
                    $pdf = PDFFile::loadHTML($view)
                        //->setPaper('A3', 'portrait');
                        ->setPaper([0, 0, 595, $altura]); // Redimensiona o PDF
                    return $pdf->download($nome_img.".pdf");
                } else {
                    $pdf = PDFFile::loadHTML($view)
                        ->setPaper('A3', 'portrait');
                    return $pdf->download($nome_img.".pdf");

                }
            } else {

                $pdfPath = $this->novoPdfTemporario();

                if($apenasvalores == 1) {
                    $pdf = PDFFile::loadHTML($view)
                        ->setPaper([0, 0, 595, $altura]);
                } else {
                    $pdf = PDFFile::loadHTML($view)->setPaper('A3', 'portrait');
                }
                $pdf->save($pdfPath);
                $imagemPath = storage_path("app/temp/{$nome_img}.png");
                if (file_exists($imagemPath)) {
                    unlink($imagemPath);  // Exclui a imagem anterior se ela existir
                }

                if($apenasvalores == 1) {
                    $command = "gs -sDEVICE=pngalpha -r300 -dDEVICEWIDTHPOINTS=595 -dDEVICEHEIGHTPOINTS={$altura} -dPDFFitPage -dUseCropBox -dDetectDuplicateImages -dNOTRANSPARENCY -o {$imagemPath} {$pdfPath}";
                    exec($command, $output, $status);
                } else {
                    $command = "gs -sDEVICE=pngalpha -r300 -o {$imagemPath} {$pdfPath}";
                    exec($command, $output, $status);
                }

                @unlink($pdfPath);

                if ($status !== 0 || !file_exists($imagemPath)) {
                    return response()->json(['error' => 'Falha ao gerar a imagem.'], 500);
                }

                return response()->download($imagemPath)->deleteFileAfterSend(true);

            }
        } else {

            $layout = auth()->user()->layout_id;
            $layout_user = in_array($layout, [1, 2, 3, 4]) ? $layout : 1;
            $viewName = "cotacao.modelo-ambulatorial{$layout_user}";

            $layout_folder = auth()->user()->isFolder() ?: '';

            $frase = "Ambulatorial ".$odonto_frase;

            $imagem_user = "";
            $image = auth()->user()->imagem;
            if($image != "" && \Illuminate\Support\Facades\Storage::disk('public')->exists($image)) {
                $imagem_user = "storage/".auth()->user()->imagem;
            }

            $dados = Tabela::select('tabelas.*')
                ->selectRaw("CASE $sql END AS quantidade")
                ->join('faixa_etarias', 'faixa_etarias.id', '=', 'tabelas.faixa_etaria_id')
                ->where('tabelas.tabela_origens_id', $cidade)
                ->where('tabelas.plano_id', $plano)
                ->where('tabelas.administradora_id', $operadora)
                ->where("tabelas.odonto",$odonto)
                ->where("acomodacao_id","=",3)
                ->whereIn('tabelas.faixa_etaria_id', explode(',',$keys))
                ->get();

            $codigo = CodigoAmbulatorial::where("tabela_origens_id",$cidade)
                ->where("plano_id",$plano)
                ->where("administradora_id",$operadora)
                ->where("odonto",$odonto)->first();


            $hasTabelaOrigens = Pdf::where('plano_id', $plano)
                ->where('tabela_origens_id',$cidade)
                ->exists();
            if ($hasTabelaOrigens) {
                $pdf_copar = Pdf::where('plano_id', $plano)
                    ->where('tabela_origens_id',$cidade)
                    ->first();
            } else {
                $pdf_copar = Pdf::where('plano_id', $plano)->first();
            }

            $layout = auth()->user()->layout_id;
            $layout_user = in_array($layout, [1, 2, 3, 4]) ? $layout : 1;
            $viewName = "cotacao.cotacao-ambulatorial{$layout_user}";

            $valor_desconto = 0;
            $texto_desconto = '';
            if($status_desconto) {
                $desconto = Desconto::where('plano_id', $plano)->where('tabela_origens_id', $cidade)->where('administradora_id',$operadora)->first();
                if($desconto) {
                    $valor_desconto = $desconto->valor;
                    $texto_desconto = $desconto->texto_desconto ?? ('Des. ' . (int)$desconto->valor . '% 3/meses');
                }
            }

            if(($cidade_uf == "MT" || $cidade_uf == "MS") && $plano == 3) {
                $status_excecao = true;
                $pdf_copar = PdfExcecao::where('plano_id', $plano)->first();
            } else {
                $hasTabelaOrigens = Pdf::where('plano_id', $plano)
                    ->where('tabela_origens_id',$cidade)
                    ->exists();
                if ($hasTabelaOrigens) {
                    $pdf_copar = Pdf::where('plano_id', $plano)
                        ->where('tabela_origens_id',$cidade)
                        ->first();

                    if($pdf_copar->linha02) {
                        $itens = explode('|', $pdf_copar->linha02);
                        $itensFormatados = array_map(function($item) {
                            return trim($item); // Remove espaços extras
                        }, $itens);
                        $linha_01 = $itensFormatados[0];
                        $linha_02 = $itensFormatados[1];
                    }


                } else {
                    $pdf_copar = Pdf::where('plano_id', $plano)->first();
                    if(isset($pdf_copar->linha02) && $pdf_copar->linha02) {
                        $itens = explode('|', $pdf_copar->linha02);
                        $itensFormatados = array_map(function($item) {
                            return trim($item); // Remove espaços extras
                        }, $itens);
                        $linha_01 = $itensFormatados[0];
                        $linha_02 = $itensFormatados[1];
                    }

                }
            }


            $com_coparticipacao_amb = request()->comcoparticipacao == "true" ? 1 : 0;
            $sem_coparticipacao_amb = request()->semcoparticipacao == "true" ? 1 : 0;

            $view = \Illuminate\Support\Facades\View::make($viewName,[
                'apelido_plano' => Plano::find($plano)?->apelido,
                'rotulo_com_copart' => RotuloCotacao::resolver(auth()->user(), 'com_copart', null, null),
                'rotulo_copart_parcial' => RotuloCotacao::resolver(auth()->user(), 'copart_parcial', null, null),
                'com_coparticipacao' => $com_coparticipacao_amb,
                'sem_coparticipacao' => $sem_coparticipacao_amb,
                'image' => $imagem_user,
                'dados' => $dados,
                'codigo' => $codigo,
                'folder' => $layout_folder,
                'pdf' => $pdf_copar,
                'plano_nome' => "Individual",
                'linha_01' => $linha_01,
                'linha_02' => $linha_02,
                'nome' => $nome,
                'desconto' => $status_desconto,
                'valor_desconto' => $valor_desconto,
                'texto_desconto' => $texto_desconto,
                'cidade' => $cidade_nome,
                'plano' => $plano_nome,
                'odonto_frase' => $odonto_frase,
                'administradora' => $admin_nome,
                'frase' => $frase,
                'carencia' => $status_carencia,
                'status_desconto' => $status_desconto,
                'odonto' => $odonto,
                'celular' => $celular,
                'linhas' => $linhas,
                'corretora' => $corretora
            ]);

            $nome_img = "orcamento_". date('d') . "_" . date('m') . "_" . date("Y") . "_" . date('H') . "_" . date("i") . "_" . date("s")."_" . uniqid();
            if($tipo_documento == "pdf") {

                $pdf = PDFFile::loadHTML($view)
                    ->setPaper('A3', 'portrait');
                return $pdf->download($nome_img.".pdf");

            } else {

                $pdfPath = $this->novoPdfTemporario();
                $pdf = PDFFile::loadHTML($view)->setPaper('A3', 'portrait');
                $pdf->save($pdfPath);
                $imagemPath = storage_path("app/temp/{$nome_img}.png");

                if (file_exists($imagemPath)) {
                    unlink($imagemPath);  // Exclui a imagem anterior se ela existir
                }

                $command = "gs -sDEVICE=pngalpha -r300 -o {$imagemPath} {$pdfPath}";  // -r150 é a resolução, pode ser ajustada

                exec($command, $output, $status);

                @unlink($pdfPath);

                if ($status !== 0 || !file_exists($imagemPath)) {
                    return response()->json(['error' => 'Falha ao gerar a imagem.'], 500);
                }

                return response()->download($imagemPath)->deleteFileAfterSend(true);
            }
        }
    }



    public function criarPDFvolho()
    {
        $com_coparticipacao = request()->comcoparticipacao  == "true" ? 1 : 0;
        $sem_coparticipacao = request()->semcoparticipacao  == "true" ? 1 : 0;
        $apenasvalores      = request()->apenasvalores      == "true" ? 1 : 0;

        $layout = Layout::find(auth()->user()->layout_id);
        $ambulatorial = request()->ambulatorial;
        $cidade = request()->tabela_origem;
        $plano = request()->plano;
        $operadora = request()->operadora;
        $odonto = request()->odonto;
        $sql = "";
        $chaves = [];
        $linhas = 0;

        foreach(request()->faixas[0] as $k => $v) {
            if($v != null AND $v != 0) {
                $sql .= " WHEN tabelas.faixa_etaria_id = {$k} THEN {$v} ";
                $chaves[] = $k;
            }
        }


        $linhas = count($chaves);
        $cidade_nome = TabelaOrigens::find($cidade)->nome;
        $plano_nome = RotuloCotacao::resolver(auth()->user(), 'nome_plano', (int) $plano, Plano::find($plano)->nome);

        $cidade_uf = TabelaOrigens::find($cidade)->uf;
        $status_excecao = false;
        if(($cidade_uf == "MT" || $cidade_uf == "MS") && $plano == 3) {
            $status_excecao = true;
            $pdf_copar = PdfExcecao::where('plano_id', $plano)->first();
        } else {
            $hasTabelaOrigens = Pdf::where('plano_id', $plano)
                ->where('tabela_origens_id',$cidade)
                ->exists();
            if ($hasTabelaOrigens) {
                $pdf_copar = Pdf::where('plano_id', $plano)
                    ->where('tabela_origens_id',$cidade)
                    ->first();
            } else {
                $pdf_copar = Pdf::where('plano_id', $plano)->first();
            }
        }

        $admin_nome = Administradora::find($operadora)->nome;
        $odonto_frase = $odonto == 1 ? " c/ Odonto" : " s/ Odonto";
        $frase = $plano_nome.$odonto_frase;
        $keys = implode(",",$chaves);
        $image_user = "";
        if(auth()->user()->imagem) {
            //$image_user = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path(auth()->user()->image)));
            //$image_user = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path("storage/".auth()->user()->imagem)));
            $image_user = public_path("storage/".auth()->user()->imagem);
        }
        $nome = auth()->user()->name;
        $celular = auth()->user()->phone;
        $corretora = auth()->user()->corretora_id;
        $status_carencia = request()->status_carencia == "true" ? 1 : 0;
        $status_desconto = request()->status_desconto == "true" ? 1 : 0;
        if($ambulatorial == 0) {
            $dados = Tabela::select('tabelas.*')
                ->selectRaw("CASE $sql END AS quantidade")
                ->join('faixa_etarias', 'faixa_etarias.id', '=', 'tabelas.faixa_etaria_id')
                ->where('tabelas.tabela_origens_id', $cidade)
                ->where('tabelas.plano_id', $plano)
                ->where('tabelas.administradora_id', $operadora)
                ->where("tabelas.odonto",$odonto)
                ->where("acomodacao_id","!=",3)
                ->whereIn('tabelas.faixa_etaria_id', explode(',', $keys))
                ->get();

            //dd($image_user);

            //$carencias = Carencia::where("plano_id",$plano)->get();
            $base64Image = "";
            $view = \Illuminate\Support\Facades\View::make("cotacao.cotacao3",[
                'com_coparticipacao' => $com_coparticipacao,
                'sem_coparticipacao' => $sem_coparticipacao,
                'apenas_valores' => $apenasvalores,
                'base64Image' => $base64Image,
                'layout' => $layout,
                'carencias' => "",
                'image' => $image_user,
                'dados' => $dados,
                'pdf' => $pdf_copar,
                'nome' => $nome,
                'cidade' => $cidade_nome,
                'plano_nome' => $plano_nome,
                'odonto_frase' => $odonto_frase,
                'administradora' => $admin_nome,
                'frase' => $frase,
                'status_carencia' => $status_carencia,
                'status_desconto' => $status_desconto,
                'odonto' => $odonto,
                'celular' => $celular,
                'status_excecao' => $status_excecao,
                'linhas' => $linhas,
                'corretora' => $corretora
            ]);

            $view->with('background_image', public_path('semlogo.png'));

            $nome_img = "orcamento_". date('d') . "_" . date('m') . "_" . date("Y") . "_" . date('H') . "_" . date("i") . "_" . date("s")."_" . uniqid();
            $pdfPath = storage_path('app/temp/temp.pdf');
            $pdf = PDFFile::loadHTML($view)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isPhpEnabled' => true,
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'dpi' => 300,
                    'defaultPaperSize' => 'a4',
                    'debugCss' => false,
                    'viewportSize' => '1920x1080'
                ]);








            $imagemPath = storage_path("app/temp/{$nome_img}.png");
            $pdf->save($pdfPath);

            if (file_exists($imagemPath)) {
                unlink($imagemPath);  // Exclui a imagem anterior se ela existir
            }
            //$command = "gs -sDEVICE=pngalpha -r300 -dUseCropBox -o {$imagemPath} {$pdfPath}";
            $command = "gs -sDEVICE=pngalpha -r300 -dUseCropBox -dPDFFitPage -o {$imagemPath} {$pdfPath}";


            exec($command, $output, $status);


            if ($status !== 0 || !file_exists($imagemPath)) {
                return response()->json(['error' => 'Falha ao gerar a imagem.'], 500);
            }

            return response()
                ->download($imagemPath)
                ->deleteFileAfterSend(true);












//            $nome_img = "orcamento_". date('d') . "_" . date('m') . "_" . date("Y") . "_" . date('H') . "_" . date("i") . "_" . date("s")."_" . uniqid();
//            $pdfPath = storage_path('app/temp/temp.pdf');
//            PDFFile::loadHTML($view)->save($pdfPath);
//            $imagemPath = storage_path("app/temp/{$nome_img}.png");
//
//            if (file_exists($imagemPath)) {
//                unlink($imagemPath);  // Exclui a imagem anterior se ela existir
//            }
//
//            $command = "gs -sDEVICE=pngalpha -r300 -o {$imagemPath} {$pdfPath}";  // -r150 é a resolução, pode ser ajustada
//
//            exec($command, $output, $status);
//
//
//            if ($status !== 0 || !file_exists($imagemPath)) {
//                return response()->json(['error' => 'Falha ao gerar a imagem.'], 500);
//            }
//
//            return response()
//                ->download($imagemPath)
//                ->deleteFileAfterSend(true);






        } else {

            $dados = Tabela::select('tabelas.*')
                ->selectRaw("CASE $sql END AS quantidade")
                ->join('faixa_etarias', 'faixa_etarias.id', '=', 'tabelas.faixa_etaria_id')
                ->where('tabelas.tabela_origens_id', $cidade)
                ->where('tabelas.plano_id', $plano)
                ->where('tabelas.administradora_id', $operadora)
                ->where("tabelas.odonto",$odonto)
                ->where("acomodacao_id","=",3)
                ->whereIn('tabelas.faixa_etaria_id', explode(',', $keys))
                ->get();
            $hasTabelaOrigens = Pdf::where('plano_id', $plano)
                ->where('tabela_origens_id',$cidade)
                ->exists();
            if ($hasTabelaOrigens) {
                $pdf_copar = Pdf::where('plano_id', $plano)
                    ->where('tabela_origens_id',$cidade)
                    ->first();
            } else {
                $pdf_copar = Pdf::where('plano_id', $plano)->first();
            }
            $view = \Illuminate\Support\Facades\View::make("cotacao.cotacao-ambulatorial-pdf",[
                'apelido_plano' => Plano::find($plano)?->apelido,
                'rotulo_com_copart' => RotuloCotacao::resolver(auth()->user(), 'com_copart', null, null),
                'rotulo_copart_parcial' => RotuloCotacao::resolver(auth()->user(), 'copart_parcial', null, null),
                'image' => $image_user,
                'dados' => $dados,
                'pdf' => $pdf_copar,
                'nome' => $nome,
                'cidade' => $cidade_nome,
                'plano' => $plano_nome,
                'odonto_frase' => $odonto_frase,
                'administradora' => $admin_nome,
                'frase' => $frase,
                'status_carencia' => $status_carencia,
                'status_desconto' => $status_desconto,
                'odonto' => $odonto,
                'celular' => $celular,
                'linhas' => $linhas,
                'corretora' => $corretora
            ]);
            $pdf = PDFFile::loadHTML($view);
            return $pdf->stream("teste.pdf");
        }
    }

}
