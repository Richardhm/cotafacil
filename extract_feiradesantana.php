<?php
ini_set('display_errors', 1);
ini_set('memory_limit', '1024M');
set_time_limit(900);

require __DIR__ . '/vendor/autoload.php';
$app    = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PdfImporter\Extractors\PdfTextExtractor;

$extractor = new PdfTextExtractor();

$individualPages = $extractor->extractPages(__DIR__ . '/public/Individual.pdf');
$ssPages         = $extractor->extractPages(__DIR__ . '/public/Super_Simples.pdf');
$ambPages        = $extractor->extractPages(__DIR__ . '/public/Ambulatorial.pdf');

$out = [
    'individual_p27' => $individualPages[27] ?? null,
    'ss_p20'         => $ssPages[20] ?? null,
    'amb_p33'        => $ambPages[33] ?? null,
];

file_put_contents(__DIR__ . '/public/feiradesantana_raw.json', json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Done\n";
