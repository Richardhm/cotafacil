<?php

namespace App\Services\PdfImporter\Writers;

use App\Services\PdfImporter\DTOs\ImportResultDto;
use App\Services\PdfImporter\DTOs\PageDto;
use App\Services\PdfImporter\Resolvers\AdminResolver;
use App\Services\PdfImporter\Resolvers\CityResolver;
use App\Services\PdfImporter\Resolvers\PlanResolver;
use Illuminate\Support\Facades\DB;

class TableWriter
{
    private const ACOM_APARTAMENTO = 1;
    private const ACOM_ENFERMARIA  = 2;
    private const ACOM_AMBULATORIAL = 3;

    public function __construct(
        private readonly AdminResolver $adminResolver,
        private readonly CityResolver  $cityResolver,
        private readonly PlanResolver  $planResolver,
    ) {}

    /**
     * @param PageDto[] $pages
     */
    public function write(array $pages, ImportResultDto $result): void
    {
        DB::transaction(function () use ($pages, $result) {
            foreach ($pages as $page) {
                $this->writePage($page, $result);
            }
        });
    }

    private function writePage(PageDto $page, ImportResultDto $result): void
    {
        $adminId  = $this->adminResolver->resolve($page->administradora);
        $cidadeId = $this->cityResolver->resolve($page->cidade, $page->uf);

        if (!$adminId || !$cidadeId) {
            return; // Validated earlier; skip silently
        }

        foreach ($page->tables as $table) {
            $planoId = $this->planResolver->resolve($table->plano);
            if (!$planoId) {
                continue;
            }

            $copat  = $table->coparticipacao;
            $odonto = $table->odonto ? 1 : 0;

            $label = "{$page->cidade} / {$table->plano} / copat={$copat} / odonto={$odonto}";

            // Insere por acomodação: primeiro TODOS os Apartamento (faixas 1–10),
            // depois TODOS os Enfermaria, depois TODOS os Ambulatorial.
            $acomodacoes = [
                self::ACOM_APARTAMENTO  => fn($row) => $row->valorApartamento,
                self::ACOM_ENFERMARIA   => fn($row) => $row->valorEnfermaria,
                self::ACOM_AMBULATORIAL => fn($row) => $row->valorAmbulatorial,
            ];

            $now = now()->format('Y-m-d H:i:s');

            foreach ($acomodacoes as $acomId => $valorFn) {
                foreach ($table->rows as $row) {
                    $base = [
                        'administradora_id' => $adminId,
                        'tabela_origens_id' => $cidadeId,
                        'plano_id'          => $planoId,
                        'coparticipacao'    => $copat,
                        'odonto'            => $odonto,
                        'faixa_etaria_id'   => $row->faixaEtariaId,
                    ];

                    $valor    = round($valorFn($row), 2);
                    $existing = DB::table('tabelas')
                        ->where($base + ['acomodacao_id' => $acomId])
                        ->first();

                    if ($existing) {
                        DB::table('tabelas')
                            ->where('id', $existing->id)
                            ->update(['valor' => $valor, 'updated_at' => now()]);

                        if ($acomId === self::ACOM_APARTAMENTO) {
                            $result->tabelasAtualizadas++;
                        }
                    } else {
                        DB::table('tabelas')->insert(
                            $base + ['acomodacao_id' => $acomId, 'valor' => $valor, 'created_at' => now(), 'updated_at' => now()]
                        );

                        if ($acomId === self::ACOM_APARTAMENTO) {
                            $result->tabelasCadastradas++;
                        }
                    }

                    $result->registrosGravados++;

                    // Acumula SQL para produção (gerado independente do estado local)
                    $result->sqlInsertRows[] = "({$adminId}, {$cidadeId}, {$planoId}, {$acomId}, {$row->faixaEtariaId}, {$copat}, {$odonto}, {$valor}, '{$now}', '{$now}')";

                    $result->sqlUpdateRows[] = "UPDATE `tabelas` SET `valor` = {$valor}, `updated_at` = NOW() WHERE `administradora_id` = {$adminId} AND `tabela_origens_id` = {$cidadeId} AND `plano_id` = {$planoId} AND `acomodacao_id` = {$acomId} AND `faixa_etaria_id` = {$row->faixaEtariaId} AND `coparticipacao` = {$copat} AND `odonto` = {$odonto};";
                }
            }

            $result->addDetalhe("✓ {$label}: " . count($table->rows) . " faixas gravadas.");
        }
    }
}
