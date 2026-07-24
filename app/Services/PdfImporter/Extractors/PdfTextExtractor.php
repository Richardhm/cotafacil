<?php

namespace App\Services\PdfImporter\Extractors;

use Smalot\PdfParser\Parser;

class PdfTextExtractor
{
    /**
     * Returns page text indexed by page number (1-based).
     *
     * @return array<int, string>
     */
    public function extractPages(string $filePath): array
    {
        $parser = new Parser();
        $pdf    = $parser->parseFile($filePath);

        $pages = [];
        foreach ($pdf->getPages() as $index => $page) {
            $pages[$index + 1] = $page->getText();
        }

        return $pages;
    }

    public function detectsOperator(string $pageText): ?string
    {
        // Price pages always contain "TABELA DE VENDAS PROMOCIONAL" or "M I X".
        // Check this BEFORE the REAJUSTE guard: Super Simples pages embed the reajuste
        // age-band column inline on the same page as the prices, so the guard would
        // otherwise discard all of them.
        if (str_contains($pageText, 'M I X') || str_contains($pageText, 'TABELA DE VENDAS PROMOCIONAL')) {
            return 'hapvida';
        }

        // Standalone reajuste pages (Individual PDF even-numbered pages) have only
        // age-band data and no price table — skip them silently.
        if (str_contains($pageText, 'REAJUSTE POR MUDANÇA DE FAIXA ETÁRIA')) {
            return null;
        }

        return null;
    }
}
