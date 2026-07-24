<?php

namespace App\Services\ImportacaoAssistida;

use App\Services\ImportacaoAssistida\DTOs\RegistroDto;
use Illuminate\Support\Facades\DB;

class Comparator
{
    private const HAPVIDA_ID = 4;

    /**
     * Loads all existing tabelas records for the city and marks each RegistroDto
     * as 'inserir', 'atualizar', or 'igual'.
     *
     * @param RegistroDto[] $registros
     */
    public function compare(array &$registros, int $cidadeId): void
    {
        $existing = DB::table('tabelas')
            ->where('administradora_id', self::HAPVIDA_ID)
            ->where('tabela_origens_id', $cidadeId)
            ->get()
            ->keyBy(fn($r) => $this->key(
                $r->plano_id, $r->acomodacao_id, $r->faixa_etaria_id, $r->coparticipacao, $r->odonto
            ));

        foreach ($registros as $registro) {
            $key    = $this->key(
                $registro->planoId, $registro->acomodacaoId,
                $registro->faixaEtariaId, $registro->coparticipacao, $registro->odonto
            );
            $dbRow  = $existing[$key] ?? null;

            if ($dbRow === null) {
                $registro->status  = 'inserir';
                continue;
            }

            $registro->valorDb = (float) $dbRow->valor;
            $registro->dbId    = $dbRow->id;
            $registro->status  = abs($registro->valorPdf - $registro->valorDb) < 0.01 ? 'igual' : 'atualizar';
        }
    }

    private function key(int $planoId, int $acomId, int $faixaId, int $copat, int $odonto): string
    {
        return "{$planoId}_{$acomId}_{$faixaId}_{$copat}_{$odonto}";
    }
}
