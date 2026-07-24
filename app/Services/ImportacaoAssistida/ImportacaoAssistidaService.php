<?php

namespace App\Services\ImportacaoAssistida;

use App\Services\ImportacaoAssistida\DTOs\CidadeResultDto;
use App\Services\ImportacaoAssistida\DTOs\RegistroDto;
use App\Services\ImportacaoAssistida\Readers\AmbulatorialReader;
use App\Services\ImportacaoAssistida\Readers\IndividualReader;
use App\Services\ImportacaoAssistida\Readers\SuperSimplesReader;
use App\Services\PdfImporter\Extractors\PdfTextExtractor;
use App\Services\PdfImporter\Resolvers\CityResolver;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportacaoAssistidaService
{
    private const HAPVIDA_ID = 4;

    private const PDF_PATHS = [
        'individual'    => 'Individual.pdf',
        'super_simples' => 'Super_Simples.pdf',
        'ambulatorial'  => 'Ambulatorial.pdf',
    ];

    public function __construct(
        private readonly PdfTextExtractor   $extractor,
        private readonly CityResolver       $cityResolver,
        private readonly IndividualReader   $individualReader,
        private readonly SuperSimplesReader $superSimplesReader,
        private readonly AmbulatorialReader $ambulatorialReader,
        private readonly Comparator         $comparator,
    ) {}

    /**
     * Extrai e compara com o banco sem gravar nada.
     */
    public function preview(string $pdfType, int $page, string $cidade, string $uf): CidadeResultDto
    {
        $pageText  = $this->extractPage($pdfType, $page);
        $registros = $this->parseWithReader($pdfType, $pageText);
        $cidadeId  = $this->cityResolver->resolve($cidade, $uf);

        $result = new CidadeResultDto(
            cidade:           $cidade,
            uf:               strtoupper($uf),
            administradoraId: self::HAPVIDA_ID,
            cidadeId:         $cidadeId,
            cidadeCriada:     false,
            registros:        $registros,
        );

        if ($cidadeId === null) {
            $result->addAviso("Cidade '{$cidade} - {$uf}' não encontrada em tabela_origens. Será criada ao importar.");
        } else {
            $this->comparator->compare($result->registros, $cidadeId);
        }

        $this->computeCounters($result);
        $this->generateSql($result, $cidadeId ?? 0);

        return $result;
    }

    /**
     * Extrai, compara, grava no banco local e gera SQL para produção.
     */
    public function importar(string $pdfType, int $page, string $cidade, string $uf): CidadeResultDto
    {
        $pageText = $this->extractPage($pdfType, $page);
        $registros = $this->parseWithReader($pdfType, $pageText);

        ['id' => $cidadeId, 'created' => $cidadeCriada] = $this->cityResolver->resolveOrCreate($cidade, $uf);

        $result = new CidadeResultDto(
            cidade:           $cidade,
            uf:               strtoupper($uf),
            administradoraId: self::HAPVIDA_ID,
            cidadeId:         $cidadeId,
            cidadeCriada:     $cidadeCriada,
            registros:        $registros,
        );

        if ($cidadeCriada) {
            $result->addAviso("Cidade '{$cidade} - {$uf}' criada em tabela_origens (id={$cidadeId}).");
        }

        $this->comparator->compare($result->registros, $cidadeId);
        $this->computeCounters($result);
        $this->generateSql($result, $cidadeId);
        $this->writeToDb($result->registros, $cidadeId);

        return $result;
    }

    /**
     * Retorna o texto bruto de uma página para análise (útil para o AmbulatorialReader).
     */
    public function analisarPagina(string $pdfType, int $page): array
    {
        $pageText = $this->extractPage($pdfType, $page);

        if ($pdfType === 'ambulatorial') {
            return $this->ambulatorialReader->analyzeRaw($pageText);
        }

        return ['texto_bruto' => $pageText];
    }

    // ─── Private helpers ───────────────────────────────────────────────────

    private function extractPage(string $pdfType, int $page): string
    {
        $path  = $this->getPdfPath($pdfType);
        $pages = $this->extractor->extractPages($path);

        if (!isset($pages[$page])) {
            throw new RuntimeException("Página {$page} não encontrada no PDF '{$pdfType}'.");
        }

        return $pages[$page];
    }

    /** @return RegistroDto[] */
    private function parseWithReader(string $pdfType, string $pageText): array
    {
        return match ($pdfType) {
            'individual'    => $this->individualReader->parse($pageText),
            'super_simples' => $this->superSimplesReader->parse($pageText),
            'ambulatorial'  => $this->ambulatorialReader->parse($pageText),
            default         => throw new RuntimeException("Tipo de PDF desconhecido: '{$pdfType}'."),
        };
    }

    private function getPdfPath(string $pdfType): string
    {
        $filename = self::PDF_PATHS[$pdfType] ?? null;

        if ($filename === null) {
            throw new RuntimeException("Tipo de PDF desconhecido: '{$pdfType}'.");
        }

        $path = public_path($filename);

        if (!file_exists($path)) {
            throw new RuntimeException("Arquivo não encontrado: {$path}");
        }

        return $path;
    }

    private function computeCounters(CidadeResultDto $result): void
    {
        foreach ($result->registros as $r) {
            match ($r->status) {
                'inserir'   => $result->totalInserir++,
                'atualizar' => $result->totalAtualizar++,
                'igual'     => $result->totalIgual++,
                default     => null,
            };
        }
    }

    private function generateSql(CidadeResultDto $result, int $cidadeId): void
    {
        $now        = now()->format('Y-m-d H:i:s');
        $adminId    = self::HAPVIDA_ID;
        $insertRows = [];
        $updateRows = [];

        foreach ($result->registros as $r) {
            if ($r->status === 'igual') {
                continue;
            }

            $valor = number_format($r->valorPdf, 2, '.', '');

            if ($r->status === 'inserir') {
                $insertRows[] = "({$adminId}, {$cidadeId}, {$r->planoId}, {$r->acomodacaoId}, {$r->faixaEtariaId}, {$r->coparticipacao}, {$r->odonto}, {$valor}, '{$now}', '{$now}')";
            } else {
                $updateRows[] = "UPDATE `tabelas` SET `valor` = {$valor}, `updated_at` = NOW()" .
                    " WHERE `administradora_id` = {$adminId}" .
                    " AND `tabela_origens_id` = {$cidadeId}" .
                    " AND `plano_id` = {$r->planoId}" .
                    " AND `acomodacao_id` = {$r->acomodacaoId}" .
                    " AND `faixa_etaria_id` = {$r->faixaEtariaId}" .
                    " AND `coparticipacao` = {$r->coparticipacao}" .
                    " AND `odonto` = {$r->odonto};";
            }
        }

        if ($insertRows) {
            $cols               = '`administradora_id`, `tabela_origens_id`, `plano_id`, `acomodacao_id`, `faixa_etaria_id`, `coparticipacao`, `odonto`, `valor`, `created_at`, `updated_at`';
            $result->sqlInsert  = "INSERT INTO `tabelas` ({$cols}) VALUES\n" . implode(",\n", $insertRows) . ';';
        }

        if ($updateRows) {
            $result->sqlUpdate = implode("\n", $updateRows);
        }
    }

    /** @param RegistroDto[] $registros */
    private function writeToDb(array $registros, int $cidadeId): void
    {
        $now     = now()->format('Y-m-d H:i:s');
        $adminId = self::HAPVIDA_ID;

        DB::transaction(function () use ($registros, $cidadeId, $adminId, $now) {
            foreach ($registros as $r) {
                if ($r->status === 'igual') {
                    continue;
                }

                $base = [
                    'administradora_id' => $adminId,
                    'tabela_origens_id' => $cidadeId,
                    'plano_id'          => $r->planoId,
                    'acomodacao_id'     => $r->acomodacaoId,
                    'faixa_etaria_id'   => $r->faixaEtariaId,
                    'coparticipacao'    => $r->coparticipacao,
                    'odonto'            => $r->odonto,
                    'valor'             => $r->valorPdf,
                ];

                if ($r->status === 'inserir') {
                    DB::table('tabelas')->insert($base + ['created_at' => $now, 'updated_at' => $now]);
                } else {
                    DB::table('tabelas')->where('id', $r->dbId)->update([
                        'valor'      => $r->valorPdf,
                        'updated_at' => $now,
                    ]);
                }
            }
        });
    }
}
