<?php

namespace App\Http\Controllers;

use App\Services\ImportacaoAssistida\DTOs\CidadeResultDto;
use App\Services\ImportacaoAssistida\ImportacaoAssistidaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ImportacaoAssistidaController extends Controller
{
    public function __construct(
        private readonly ImportacaoAssistidaService $service,
    ) {}

    public function index(): \Illuminate\View\View
    {
        return view('importacao-assistida.index');
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pdf_type' => ['required', Rule::in(['individual', 'super_simples', 'ambulatorial'])],
            'page'     => ['required', 'integer', 'min:1'],
            'cidade'   => ['required', 'string', 'max:100'],
            'uf'       => ['required', 'string', 'size:2'],
        ]);

        try {
            $result = $this->service->preview(
                $validated['pdf_type'],
                (int) $validated['page'],
                trim($validated['cidade']),
                strtoupper(trim($validated['uf'])),
            );

            return response()->json($this->formatResult($result));
        } catch (\Throwable $e) {
            return response()->json(['sucesso' => false, 'erro' => $e->getMessage()], 422);
        }
    }

    public function importar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pdf_type' => ['required', Rule::in(['individual', 'super_simples', 'ambulatorial'])],
            'page'     => ['required', 'integer', 'min:1'],
            'cidade'   => ['required', 'string', 'max:100'],
            'uf'       => ['required', 'string', 'size:2'],
        ]);

        try {
            $result = $this->service->importar(
                $validated['pdf_type'],
                (int) $validated['page'],
                trim($validated['cidade']),
                strtoupper(trim($validated['uf'])),
            );

            return response()->json($this->formatResult($result, imported: true));
        } catch (\Throwable $e) {
            return response()->json(['sucesso' => false, 'erro' => $e->getMessage()], 422);
        }
    }

    public function analisar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pdf_type' => ['required', Rule::in(['individual', 'super_simples', 'ambulatorial'])],
            'page'     => ['required', 'integer', 'min:1'],
        ]);

        try {
            $raw = $this->service->analisarPagina(
                $validated['pdf_type'],
                (int) $validated['page'],
            );

            return response()->json(['sucesso' => true, 'dados' => $raw]);
        } catch (\Throwable $e) {
            return response()->json(['sucesso' => false, 'erro' => $e->getMessage()], 422);
        }
    }

    private function formatResult(CidadeResultDto $result, bool $imported = false): array
    {
        return [
            'sucesso'         => true,
            'importado'       => $imported,
            'cidade'          => $result->cidade,
            'uf'              => $result->uf,
            'cidade_id'       => $result->cidadeId,
            'cidade_criada'   => $result->cidadeCriada,
            'total_inserir'   => $result->totalInserir,
            'total_atualizar' => $result->totalAtualizar,
            'total_igual'     => $result->totalIgual,
            'avisos'          => $result->avisos,
            'sql_insert'      => $result->sqlInsert,
            'sql_update'      => $result->sqlUpdate,
            'registros'       => array_map(fn($r) => [
                'plano_id'      => $r->planoId,
                'acomodacao_id' => $r->acomodacaoId,
                'faixa_id'      => $r->faixaEtariaId,
                'copat'         => $r->coparticipacao,
                'odonto'        => $r->odonto,
                'valor_pdf'     => $r->valorPdf,
                'valor_db'      => $r->valorDb,
                'status'        => $r->status,
            ], $result->registros),
        ];
    }
}
