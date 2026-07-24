<?php
ini_set('display_errors', 1);
ini_set('memory_limit', '768M');
set_time_limit(300);

require __DIR__ . '/vendor/autoload.php';
$app    = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PdfImporter\PdfImporterService;

$service = app(PdfImporterService::class);
$result = $service->import([
    __DIR__ . '/public/20260701 a 20260930 - Super Simples 2 a 29 vidas.pdf',
]);

$out = json_encode([
    'sucesso'            => !$result->temErros(),
    'cidadesEncontradas' => $result->cidadesEncontradas,
    'tabelasAtualizadas' => $result->tabelasAtualizadas,
    'tabelasCadastradas' => $result->tabelasCadastradas,
    'registrosGravados'  => $result->registrosGravados,
    'erros'              => $result->erros,
    'avisos'             => $result->avisos,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

file_put_contents(__DIR__ . '/public/ss_import_result.json', $out);
echo "Done\n";
