<?php

namespace App\Http\Controllers;

use App\Services\PdfImporter\PdfImporterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PdfImportController extends Controller
{
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'pdfs'   => 'required|array|min:1|max:10',
            'pdfs.*' => 'required|file|mimes:pdf|max:20480', // max 20MB each
        ]);

        $filePaths = [];
        foreach ($request->file('pdfs') as $file) {
            $filePaths[] = $file->getRealPath();
        }

        $service = new PdfImporterService();
        $result  = $service->import($filePaths);

        $status = $result->temErros() ? 422 : 200;

        return response()->json([
            'sucesso'              => !$result->temErros(),
            'cidades_encontradas'  => $result->cidadesEncontradas,
            'tabelas_atualizadas'  => $result->tabelasAtualizadas,
            'tabelas_cadastradas'  => $result->tabelasCadastradas,
            'registros_gravados'   => $result->registrosGravados,
            'erros'                => $result->erros,
            'avisos'               => $result->avisos,
            'detalhes'             => $result->detalhes,
            'sql_insert'           => $result->getSqlInsert(),
            'sql_update'           => $result->getSqlUpdate(),
        ], $status);
    }
}
