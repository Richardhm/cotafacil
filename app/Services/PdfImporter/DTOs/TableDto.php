<?php

namespace App\Services\PdfImporter\DTOs;

readonly class TableDto
{
    /**
     * @param int    $coparticipacao  0=Parcial, 1=Total, 2=Sem, 3=Referência
     * @param bool   $odonto          true=Com Odonto, false=Sem Odonto
     * @param RowDto[] $rows
     */
    public function __construct(
        public string $plano,
        public int    $coparticipacao,
        public bool   $odonto,
        public array  $rows,
    ) {}
}
