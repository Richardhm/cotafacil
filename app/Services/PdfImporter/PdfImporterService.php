<?php

namespace App\Services\PdfImporter;

use App\Services\PdfImporter\DTOs\ImportResultDto;
use App\Services\PdfImporter\DTOs\PageDto;
use App\Services\PdfImporter\Extractors\PdfTextExtractor;
use App\Services\PdfImporter\Parsers\Hapvida\HapvidaIndividualMultiCityParser;
use App\Services\PdfImporter\Parsers\Hapvida\HapvidaParser;
use App\Services\PdfImporter\Parsers\Hapvida\HapvidaSuperSimplesParser;
use App\Services\PdfImporter\Resolvers\AdminResolver;
use App\Services\PdfImporter\Resolvers\CityResolver;
use App\Services\PdfImporter\Resolvers\PlanResolver;
use App\Services\PdfImporter\Validators\ImportValidator;
use App\Services\PdfImporter\Writers\TableWriter;
use RuntimeException;
use Throwable;

class PdfImporterService
{
    private PdfTextExtractor              $extractor;
    private HapvidaSuperSimplesParser     $hapvidaSuperSimplesParser;
    private HapvidaIndividualMultiCityParser $hapvidaIndividualParser;
    private HapvidaParser                 $hapvidaParser;
    private AdminResolver                 $adminResolver;
    private CityResolver                  $cityResolver;
    private PlanResolver                  $planResolver;
    private ImportValidator               $validator;
    private TableWriter                   $writer;

    public function __construct()
    {
        $this->extractor                 = new PdfTextExtractor();
        $this->hapvidaSuperSimplesParser = new HapvidaSuperSimplesParser();
        $this->hapvidaIndividualParser   = new HapvidaIndividualMultiCityParser();
        $this->hapvidaParser             = new HapvidaParser();
        $this->adminResolver             = new AdminResolver();
        $this->cityResolver              = new CityResolver();
        $this->planResolver              = new PlanResolver();
        $this->validator                 = new ImportValidator($this->adminResolver, $this->cityResolver, $this->planResolver);
        $this->writer                    = new TableWriter($this->adminResolver, $this->cityResolver, $this->planResolver);
    }

    /**
     * Main entry point. Receives one or more file paths and processes them.
     *
     * @param string[] $filePaths
     */
    public function import(array $filePaths): ImportResultDto
    {
        $result = new ImportResultDto();
        $pages  = [];

        // ─── Extração + Interpretação ─────────────────────────────────────
        foreach ($filePaths as $filePath) {
            try {
                $rawPages = $this->extractor->extractPages($filePath);
            } catch (Throwable $e) {
                $result->addErro("Falha ao extrair texto do PDF '" . basename($filePath) . "': " . $e->getMessage());
                continue;
            }

            // Detect PDF type once per file to choose the correct discard list.
            $isSuperSimples = false;
            foreach ($rawPages as $pageText) {
                if (str_contains($pageText, 'PORTE I (de 2 a 15 vidas)')) {
                    $isSuperSimples = true;
                    break;
                }
            }

            // Super Simples Jul-Set 2026: 5=Porte I+II combinado, 11=Campina Grande (2 vals),
            //                             16=Teresina (12 vals), 34=Jaboticabal (city missing).
            // Individual Jul-Set 2026:   11, 19, 21, 27 (reajuste pages / combined pages).
            $paginasDescartadas = $isSuperSimples
                ? [5, 11, 16, 34]
                : [11, 19, 21, 27];

            foreach ($rawPages as $pageNumber => $pageText) {
                if (in_array($pageNumber, $paginasDescartadas, true)) {
                    continue;
                }

                $operator = $this->extractor->detectsOperator($pageText);

                if ($operator === null) {
                    $result->addAviso("Página {$pageNumber}: operadora não reconhecida, pulando.");
                    continue;
                }

                try {
                    $page = match ($operator) {
                        'hapvida' => $this->parseHapvida($pageText, $pageNumber),
                        default   => throw new RuntimeException("Operadora '{$operator}' sem parser implementado."),
                    };

                    $pages[] = $page;
                    $result->cidadesEncontradas++;

                } catch (Throwable $e) {
                    $result->addAviso("Página {$pageNumber}: pulando — " . $e->getMessage());
                }
            }
        }

        if (empty($pages)) {
            $result->addErro('Nenhuma página válida encontrada nos PDFs enviados.');
            return $result;
        }

        // ─── Validação ────────────────────────────────────────────────────
        $valid = $this->validator->validate($pages, $result);

        if (!$valid) {
            $result->addErro('Importação cancelada por erros de validação. Nenhum dado foi gravado.');
            return $result;
        }

        // ─── Gravação ─────────────────────────────────────────────────────
        try {
            $this->writer->write($pages, $result);
        } catch (Throwable $e) {
            $result->addErro('Falha ao gravar no banco: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Selects the correct Hapvida parser by canParse() order (most-specific first):
     *   1. HapvidaSuperSimplesParser          (Super Simples 2-29 vidas — "PORTE I")
     *   2. HapvidaIndividualMultiCityParser   (Individual multi-city PDF — "TABELA DE VENDAS PROMOCIONAL")
     *   3. HapvidaParser                      (fallback: AN.GO Individual legacy format — "M I X")
     */
    private function parseHapvida(string $text, int $pageNum): PageDto
    {
        if ($this->hapvidaSuperSimplesParser->canParse($text)) {
            return $this->hapvidaSuperSimplesParser->parse($text, $pageNum);
        }

        if ($this->hapvidaIndividualParser->canParse($text)) {
            return $this->hapvidaIndividualParser->parse($text, $pageNum);
        }

        if ($this->hapvidaParser->canParse($text)) {
            return $this->hapvidaParser->parse($text, $pageNum);
        }

        throw new RuntimeException("Formato Hapvida não reconhecido na página {$pageNum}.");
    }
}
