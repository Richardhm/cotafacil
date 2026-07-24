<?php

namespace App\Services\PdfImporter\Validators;

use App\Services\PdfImporter\DTOs\ImportResultDto;
use App\Services\PdfImporter\DTOs\PageDto;
use App\Services\PdfImporter\Resolvers\AdminResolver;
use App\Services\PdfImporter\Resolvers\CityResolver;
use App\Services\PdfImporter\Resolvers\PlanResolver;

class ImportValidator
{
    public function __construct(
        private readonly AdminResolver $adminResolver,
        private readonly CityResolver  $cityResolver,
        private readonly PlanResolver  $planResolver,
    ) {}

    /**
     * Validates a collection of PageDtos.
     * Returns true if validation passed (even with warnings).
     * Errors are blocking; warnings are informational.
     *
     * @param PageDto[] $pages
     */
    public function validate(array $pages, ImportResultDto $result): bool
    {
        foreach ($pages as $page) {
            $this->validatePage($page, $result);
        }

        return !$result->temErros();
    }

    private function validatePage(PageDto $page, ImportResultDto $result): void
    {
        $prefix = "Página {$page->pageNumber} ({$page->cidade} - {$page->uf})";

        // Administradora
        if ($this->adminResolver->resolve($page->administradora) === null) {
            $result->addErro("{$prefix}: Administradora '{$page->administradora}' não encontrada no banco. Cadastre-a primeiro.");
        }

        // Cidade — deve estar pré-cadastrada em tabela_origens; se não estiver, pula.
        if ($this->cityResolver->resolve($page->cidade, $page->uf) === null) {
            $result->addAviso("{$prefix}: Cidade '{$page->cidade} - {$page->uf}' não encontrada em tabela_origens, pulando.");
            return;
        }

        // Valor promo
        if ($page->valorPromoOdonto <= 0) {
            $result->addAviso("{$prefix}: VALOR PROMO (odonto) não encontrado ou zero. Tabelas 'Com Odonto' terão o mesmo valor que 'Sem Odonto'.");
        }

        // Tables
        foreach ($page->tables as $table) {
            if ($this->planResolver->resolve($table->plano) === null) {
                $result->addErro("{$prefix}: Plano '{$table->plano}' não encontrado no banco. Cadastre-o primeiro.");
            }

            if (count($table->rows) !== 10) {
                $result->addErro("{$prefix} / {$table->plano}: esperado 10 faixas etárias, encontrado " . count($table->rows) . ".");
            }

            foreach ($table->rows as $row) {
                if ($row->valorApartamento < 0 || $row->valorEnfermaria < 0 || $row->valorAmbulatorial < 0) {
                    $result->addErro("{$prefix} / {$table->plano}: valor negativo na faixa {$row->faixaEtariaId}.");
                }
            }
        }
    }
}
