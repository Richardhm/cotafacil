<?php

namespace App\Http\Controllers;

use App\Models\Administradora;
use App\Models\AdministradoraPlano;
use App\Models\Assinatura;
use App\Models\Cupom;
use App\Models\Desconto;
use App\Models\Pdf;
use App\Models\Plano;
use App\Models\Tabela;
use App\Models\TabelaOrigens;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;
use App\Models\PdfExcecao;


class ConfiguracoesController extends Controller
{

    public function index()
    {
        return view('configuracoes.index');
    }

    public function salvarTabela(Request $request)
    {
        foreach($request->valoresApartamento as $k => $v) {
            $tabela = new Tabela();

            $tabela->administradora_id = $request->administradora;
            $tabela->plano_id = $request->planos;
            $tabela->tabela_origens_id = $request->tabela_origem;
            $tabela->acomodacao_id = 1;

            $tabela->coparticipacao = ($request->coparticipacao == "sim" ? true : false);
            $tabela->odonto = ($request->odonto == "sim" ? true : false);
            $tabela->faixa_etaria_id = $k + 1;
            $tabela->valor = str_replace([".",","],["","."],$request->valoresApartamento[$k]);

            if(!$tabela->save()) {
                return "error";
            }
        }

        foreach($request->valoresEnfermaria as $k => $v) {
            $tabela = new Tabela();

            $tabela->administradora_id = $request->administradora;
            $tabela->plano_id = $request->planos;
            $tabela->tabela_origens_id = $request->tabela_origem;
            $tabela->acomodacao_id = 2;

            $tabela->coparticipacao = ($request->coparticipacao == "sim" ? true : false);
            $tabela->odonto = ($request->odonto == "sim" ? true : false);

            $tabela->faixa_etaria_id = $k + 1;
            $tabela->valor = str_replace([".",","],["","."],$request->valoresEnfermaria[$k]);

            if(!$tabela->save()) {
                return "error";
            }
        }

        foreach($request->valoresAmbulatorial as $k => $v) {
            $tabela = new Tabela();

            $tabela->administradora_id = $request->administradora;
            $tabela->plano_id = $request->planos;
            $tabela->tabela_origens_id = $request->tabela_origem;
            $tabela->acomodacao_id = 3;

            $tabela->coparticipacao = ($request->coparticipacao == "sim" ? true : false);
            $tabela->odonto = ($request->odonto == "sim" ? true : false);

            $tabela->faixa_etaria_id = $k + 1;
            $tabela->valor = str_replace([".",","],["","."],$request->valoresAmbulatorial[$k]);


            if(!$tabela->save()) {
                return "error";
            }
        }

        return "sucesso";

    }





    public function cidadeStore(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'uf' => 'required|string|size:2'
        ]);
        TabelaOrigens::create($request->all());
        return redirect()->back()->with('success', 'Cidade cadastrada!');
    }

    public function cidadeDestroy($id)
    {

        try {
            $cidade = TabelaOrigens::findOrFail($id);
            if ($cidade->tabelaModels()->exists()) {
                return redirect()->back()
                    ->with('error', 'Não é possível excluir: cidade possui registros vinculados!');
            }
            TabelaOrigens::destroy($id);
            return redirect()->back()->with('success', 'Cidade excluída!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao excluir: ' . $e->getMessage());
        }

    }

    public function storeAdministradora(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->all();

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store(
                '', // Não usar subdiretório
                ['disk' => 'administradoras'] // Especificar o disco
            );
            $data['logo'] = 'administradoras/' . $path; // Adiciona o caminho completo
        }

        Administradora::create($data);
        return back()->with('success', 'Administradora cadastrada!');
    }

    public function administradoraDestroy(Administradora $administradora)
    {
        try {
            if ($administradora->dependentes()->exists()) {
                return back()->with('error', 'Existem registros vinculados!');
            }


            if ($administradora->logo) {
                $filename = str_replace('administradoras/', '', $administradora->logo);
                Storage::disk('administradoras')->delete($filename);
            }

            $administradora->delete();
            return back()->with('success', 'Administradora excluída!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    public function storePlanos(Request $request)
    {

        $request->validate([
            'nome' => 'required|string|max:255',
            'empresarial' => 'nullable|boolean'
        ]);

        Plano::create([
            'nome' => $request->nome,
            'empresarial' => $request->filled('empresarial')
        ]);

        return back()->with('success', 'Plano cadastrado!');
    }

    public function planosDestroy(Plano $plano)
    {
        try {
            if ($plano->dependentes()->exists()) {
                return back()->with('error', 'Existem registros vinculados!');
            }

            $plano->delete();
            return back()->with('success', 'Plano excluído!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro: '.$e->getMessage());
        }
    }

    public function getCidades(Assinatura $assinatura)
    {
        $todasCidades = TabelaOrigens::all();
        $cidadesVinculadas = $assinatura->cidades()->pluck('tabela_origens.id')->toArray();
        $cidades = $todasCidades->map(function($cidade) use ($cidadesVinculadas) {
            return [
                'id' => $cidade->id,
                'nome' => $cidade->nome,
                'uf' => $cidade->uf,
                'vinculado' => in_array($cidade->id, $cidadesVinculadas)
            ];
        });
        return response()->json(['cidades' => $cidades]);
    }

    public function vincular(Request $request)
    {
        $request->validate([
            'assinatura_id' => 'required|exists:assinaturas,id',
            'cidade_id' => 'required|exists:tabela_origens,id'
        ]);

        $assinatura = Assinatura::find($request->assinatura_id);
        $assinatura->cidades()->syncWithoutDetaching([$request->cidade_id]);

        return response()->json(['success' => true]);
    }

    public function desvincular(Request $request)
    {
        $request->validate([
            'assinatura_id' => 'required|exists:assinaturas,id',
            'cidade_id' => 'required|exists:tabela_origens,id'
        ]);

        $assinatura = Assinatura::find($request->assinatura_id);
        $assinatura->cidades()->detach($request->cidade_id);

        return response()->json(['success' => true]);
    }

    public function storePdf(Request $request)
    {

        $request->merge([
            'linha02' => $request->linha02_part1 . '|' . $request->linha02_part2
        ]);

        $data = $request->validate([
            'plano_id' => 'required|exists:planos,id',
            'tabela_origens_id' => 'nullable|exists:tabela_origens,id',
            'linha01' => 'nullable|string|max:35',
            'linha02_part1' => 'nullable|string|max:35',
            'linha02_part2' => 'nullable|string|max:35',
            'linha03' => 'nullable|string|max:35',
            // Adicionar validação para todos os campos total/parcial
            'consultas_eletivas_total' => 'nullable|string',
            'consultas_de_urgencia_total' => 'nullable|string',
            'exames_simples_total' => 'nullable|string',
            'exames_complexos_total' => 'nullable|string',
            'terapias_especiais_total' => 'nullable|string',
            'demais_terapias_total' => 'nullable|string',
            'internacoes_total' => 'nullable|string',
            'cirurgia_total' => 'nullable|string',
            'consultas_eletivas_parcial' => 'nullable|string',
            'exames_simples_parcial' => 'nullable|string',
            'exames_complexos_parcial' => 'nullable|string',
            'terapias_especiais_parcial' => 'nullable|string',
            'demais_terapias_parcial' => 'nullable|string',
            'internacoes_parcial' => 'nullable|string',
            'cirurgia_parcial' => 'nullable|string',
            'consultas_de_urgencia_parcial' => 'nullable|string',
        ]);
        $data['linha02'] = implode('|', [
            $data['linha02_part1'] ?? '',
            $data['linha02_part2'] ?? ''
        ]);



        Pdf::create($data);
        return back()->with('success', 'Configuração salva!');
    }

    public function editPdf(Pdf $pdf)
    {
        return response()->json($pdf);
    }

    public function updatePdf(Request $request, Pdf $pdf)
    {
        $request->merge([
            'linha02' => $request->linha02_part1 . '|' . $request->linha02_part2
        ]);

        $data = $request->validate([
            'plano_id' => 'required|exists:planos,id',
            'tabela_origens_id' => 'nullable|exists:tabela_origens,id',
            'linha01' => 'nullable|string|max:35',
            'linha02_part1' => 'nullable|string|max:35',
            'linha02_part2' => 'nullable|string|max:35',
            'linha03' => 'nullable|string|max:35',
            'consultas_eletivas_total' => 'nullable|string',
            'consultas_de_urgencia_total' => 'nullable|string',
            'exames_simples_total' => 'nullable|string',
            'exames_complexos_total' => 'nullable|string',
            'terapias_especiais_total' => 'nullable|string',
            'demais_terapias_total' => 'nullable|string',
            'internacoes_total' => 'nullable|string',
            'cirurgia_total' => 'nullable|string',
            'consultas_eletivas_parcial' => 'nullable|string',
            'exames_simples_parcial' => 'nullable|string',
            'exames_complexos_parcial' => 'nullable|string',
            'terapias_especiais_parcial' => 'nullable|string',
            'demais_terapias_parcial' => 'nullable|string',
            'internacoes_parcial' => 'nullable|string',
            'cirurgia_parcial' => 'nullable|string',
            'consultas_de_urgencia_parcial' => 'nullable|string',
        ]);
        $data['linha02'] = implode('|', [
            $data['linha02_part1'] ?? '',
            $data['linha02_part2'] ?? ''
        ]);

        $pdf->update($data);
        return back()->with('success', 'Configuração atualizada!');
    }

    public function destroyPdf(Pdf $pdf)
    {
        $pdf->delete();
        return back()->with('success', 'Coparticipação excluído!');
    }


    public function mudarTabela(Request $request)
    {
        $ta = Tabela::find($request->id);
        $ta->valor = str_replace([".",","],["","."],$request->valor);
        $ta->save();
    }


    public function verificar(Request $request)
    {

        $administradora_id = $request->administradora;
        $plano_id = $request->planos;
        $tabela_origem_id = $request->tabela_origem;
        $coparticipacao = $request->coparticipacao == "sim" ? 1 : 0;
        $odonto = $request->odonto == "sim" ? 1 : 0;



        $tabela = DB::table('tabelas')
            ->where("administradora_id",$administradora_id)
            ->where("tabela_origens_id",$tabela_origem_id)
            ->where("plano_id",$plano_id)
            ->where("coparticipacao",$coparticipacao)
            ->where("odonto",$odonto)
            ->select("acomodacao_id", "valor","id")
            ->get();



        if($tabela->count() >= 1) {
            $ta = $tabela->map(function ($item) {

                $item->valor_formatado = number_format($item->valor, 2, ',', '.');
                return $item;
            });
            return $ta;
        } else {
            return "empty";
        }
    }

    public function salvar(Request $request)
    {

    }


    public function storeDesconto(Request $request)
    {
        $request->validate([
            'planos' => 'required|array|min:1',
            'planos.*' => 'exists:planos,id',
            'cidades' => 'required|array|min:1',
            'cidades.*' => 'exists:tabela_origens,id',
            'valor' => 'nullable|numeric|min:0',
            'administradora' => 'required|array|min:1',
            'administradora.*' => 'exists:administradoras,id',
        ]);

        try {
            foreach ($request->planos as $planoId) {
                foreach ($request->cidades as $cidadeId) {
                    foreach ($request->administradora as $administradoraId) {
                        $key = [
                            'plano_id'          => $planoId,
                            'tabela_origens_id' => $cidadeId,
                            'administradora_id' => $administradoraId,
                        ];
                        $existing = Desconto::where($key)->first();
                        if ($existing) {
                            $existing->update(['valor' => $request->valor]);
                        } else {
                            Desconto::create(array_merge($key, [
                                'valor'          => $request->valor,
                                'texto_desconto' => 'Des. ' . (int) $request->valor . '% 3/meses',
                            ]));
                        }
                    }
                }
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ]);
        }
    }

    public function updateTextoDesconto(Request $request, Desconto $desconto)
    {
        $request->validate(['texto_desconto' => 'required|string|max:100']);
        $desconto->update(['texto_desconto' => $request->texto_desconto]);
        return response()->json(['success' => true]);
    }

    public function destroyDesconto(Desconto $desconto)
    {
        $desconto->delete();
        return back()->with('success', 'Desconto excluído!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'assinatura_id' => 'required',
            'administradora_id' => 'required',
            'plano_id' => 'required',
            'tabela_origem_id' => 'required',
        ]);

        $assinaturaId = $request->assinatura_id;
        $administradoraId = $request->administradora_id;
        $planoId = $request->plano_id;
        $tabelas = $request->tabela_origem_id ?? "";

        $inserts = [];

        //foreach ($assinaturas as $assinaturaId) {
            //foreach ($administradoras as $administradoraId) {
                //foreach ($planos as $planoId) {
                    foreach ($tabelas as $tabelaId) {

                        // Verifica se já existe antes de adicionar no array
                        $exists = AdministradoraPlano::where([
                            'administradora_id' => $administradoraId,
                            'plano_id' => $planoId,
                            'tabela_origens_id' => $tabelaId,
                            'assinatura_id' => $assinaturaId
                        ])->exists();

                        if (!$exists) {
                            $inserts[] = [
                                'administradora_id' => $administradoraId,
                                'plano_id' => $planoId,
                                'tabela_origens_id' => $tabelaId,
                                'assinatura_id' => $assinaturaId,
                                'created_at' => now(),  // Necessário se sua tabela usa timestamps
                                'updated_at' => now(),
                            ];
                        }
                    }
                //}
            //}
        //}

        if (!empty($inserts)) {
            // insertOrIgnore por causa do índice único: dois cliques seguidos ou a mesma
            // cidade repetida no payload não podem virar erro 500 na tela.
            AdministradoraPlano::insertOrIgnore($inserts);
        }

        return response()->json(['success' => 'Associações criadas com sucesso!']);
    }

    public function detalheCarencia(Request $request)
    {
        $plano_id = $request->get('plano_id');
        $tabela_origens_id = $request->get('tabela_origens_id');

        $plano = \App\Models\Plano::findOrFail($plano_id);
        $cidade = \App\Models\TabelaOrigens::findOrFail($tabela_origens_id);

        $carencias = \App\Models\Carencia::where('plano_id', $plano_id)
            ->where('tabela_origens_id', $tabela_origens_id)
            ->orderBy('id')
            ->get();

        return view('carencia.detalhe', compact('plano', 'cidade', 'carencias'));
    }

    public function deleteGrupoCarencia(Request $request)
    {
        $plano_id = $request->get('plano_id');
        $tabela_origens_id = $request->get('tabela_origens_id');

        \App\Models\Carencia::where('plano_id', $plano_id)
            ->where('tabela_origens_id', $tabela_origens_id)
            ->delete();

        return redirect()->route('configuracoes.index')
            ->with('success', 'Grupo de carências excluído com sucesso!');
    }





    public function storeCarencia(Request $request)
    {
        $plano_id           = $request->input('plano_id');
        $tabela_origens_id  = $request->input('tabela_origens_id');

        $carencia = \App\Models\Carencia::where('plano_id', $plano_id)
            ->where('tabela_origens_id', $tabela_origens_id)
            ->count();

        if($carencia >= 1) {
            \App\Models\Carencia::where('plano_id', $plano_id)
                ->where('tabela_origens_id', $tabela_origens_id)
                ->delete();
        }

        // Cria os 6 registros novos
        for ($i = 1; $i <= 6; $i++) {
            \App\Models\Carencia::create([
                'plano_id'          => $plano_id,
                'tabela_origens_id' => $tabela_origens_id,
                'tempo'             => $request->input("tempo_{$i}"),
                'detalhe'           => $request->input("detalhe_{$i}"),

            ]);
        }
        return response()->json([
            'success' => true
        ]);
    }



    /**
     * Vínculos de uma assinatura agrupados por cidade, para o modal de "Ver Vínculos".
     */
    public function vinculosPorAssinatura($assinatura)
    {
        $vinculos = AdministradoraPlano::with(['administradora', 'plano', 'tabelaOrigem'])
            ->select('administradora_planos.*')
            ->leftJoin('tabela_origens', 'tabela_origens.id', '=', 'administradora_planos.tabela_origens_id')
            ->leftJoin('administradoras', 'administradoras.id', '=', 'administradora_planos.administradora_id')
            ->leftJoin('planos', 'planos.id', '=', 'administradora_planos.plano_id')
            ->where('administradora_planos.assinatura_id', $assinatura)
            ->orderByRaw('tabela_origens.nome IS NULL')
            ->orderBy('tabela_origens.nome')
            ->orderBy('administradoras.nome')
            ->orderBy('planos.nome')
            ->get();

        // groupBy preserva a ordem de aparição, então as cidades já saem ordenadas pela query.
        $cidades = $vinculos
            ->groupBy(fn ($vinculo) => $vinculo->tabela_origens_id ?? 0)
            ->map(function ($itens, $cidadeId) {
                return [
                    'cidade_id' => (int) $cidadeId,
                    'cidade' => $itens->first()->tabelaOrigem->nome ?? 'Sem cidade',
                    'total' => $itens->count(),
                    'itens' => $itens->map(fn ($vinculo) => [
                        'id' => $vinculo->id,
                        'administradora' => $vinculo->administradora->nome ?? 'N/A',
                        'plano' => $vinculo->plano->nome ?? 'N/A',
                    ])->values(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'total' => $vinculos->count(),
            'cidades' => $cidades,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $vinculo = AdministradoraPlano::findOrFail($id);
        $vinculo->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Associação removida!');
    }

    public function storeCupon(Request $request)
    {

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            //'codigo' => 'required|string|size:10|unique:cupons',
            'desconto_plano' => 'required|min:0',
            //'desconto_extra' => 'required|min:0',
            'duracao_horas' => 'required|min:0|max:8760',
            'duracao_minutos' => 'required|min:0|max:59',
            'duracao_segundos' => 'required|min:0|max:59',
            'usos_maximos' => 'nullable|integer|min:1',
            'ativo' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $codigo = $this->gerarCodigoUnico();

            $horas = (int)$request->duracao_horas;
            $minutos = (int)$request->duracao_minutos;
            $segundos = (int)$request->duracao_segundos;

            $validade = Carbon::now()
                ->addHours($horas)
                ->addMinutes($minutos)
                ->addSeconds($segundos);

            $cupom = Cupom::create([
                'codigo' => $codigo,
                'desconto_plano' => str_replace([".",","],["","."],$request->desconto_plano),
                'desconto_extra' => 0,
                'duracao_horas' => $request->duracao_horas,
                'duracao_minutos' => $request->duracao_minutos,
                'duracao_segundos' => $request->duracao_segundos,
                'validade' => $validade->setTimezone(config('app.timezone')),
                'usos_maximos' => $request->usos_maximos ?? 1,
                'ativo' => $request->ativo
            ]);

            $dado = Cupom::find($cupom->id);



            return response()->json([
                'success' => true,
                'codigo' => $dado->codigo,
                'validade' => $dado->validade->format('Y-m-d H:i:s'),
                'valor_plano' => $dado->desconto_plano,
                //'valor_desconto' => $dado->desconto_extra,
                'message' => 'Cupom criado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar cupom: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validar(Request $request) {
        $cupom = Cupom::where('codigo', $request->codigo_cupom)
            ->where('ativo', 1)
            ->where('validade', '>=', now()->format('Y-m-d H:i:s'))
            ->whereRaw('usos < usos_maximos')
            ->first();



        if (!$cupom) {
            return response()->json(['success'=>false, 'mensagem'=>'Cupom inválido ou expirado.']);
        }

        // Retorna os dados que o JS vai usar
        return response()->json([
            'success' => true,
            'desconto_plano' => $cupom->desconto_plano,
            'desconto_extra' => $cupom->desconto_extra,
            'mensagem' => 'Cupom aplicado com sucesso!'
        ]);
    }

    private function gerarCodigoUnico()
    {
        $tentativas = 0;
        $max_tentativas = 5;

        do {
            $codigo = $this->gerarCodigoAleatorio();
            $tentativas++;
        } while (Cupom::where('codigo', $codigo)->exists() && $tentativas < $max_tentativas);

        if ($tentativas >= $max_tentativas) {
            throw new \Exception('Não foi possível gerar um código único após várias tentativas');
        }

        return $codigo;
    }

    private function gerarCodigoAleatorio()
    {
        $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        return substr(str_shuffle(str_repeat($caracteres, 5)), 0, 10);
    }


    public function planosPorAdministradora(Request $request)
    {

        $administradoraIds = $request->input('administradora_id');

        $planos = \DB::table('tabelas')
            ->join('planos', 'planos.id', '=', 'tabelas.plano_id')
            ->where('tabelas.administradora_id', $administradoraIds)
            ->select('planos.id', 'planos.nome')
            ->groupBy('planos.id', 'planos.nome')
            ->get();

        return response()->json($planos);
    }

    public function tabelasPorAdminPlano(Request $request)
    {
        $administradoraIds = $request->input('administradora_id');
        $planoIds = $request->input('plano_id');

        $tabelas = \DB::table('tabelas')
            ->join('tabela_origens', 'tabela_origens.id', '=', 'tabelas.tabela_origens_id')
            ->where('tabelas.administradora_id', $administradoraIds)
            ->where('tabelas.plano_id', $planoIds)
            ->select('tabela_origens.id', 'tabela_origens.nome')
            ->groupBy('tabela_origens.id', 'tabela_origens.nome')
            ->get();

        return response()->json($tabelas);
    }


    public function storePdfExcecao(Request $request)
    {
        PdfExcecao::create($request->all());

        return redirect()->back()->with('success', 'Registro salvo!');
    }

    public function destroyPdfExcecao($id)
    {
        PdfExcecao::destroy($id);




        return redirect()->back()->with('success', 'Registro excluído!');
    }




}
