<?php

namespace App\Services\PdfImporter\DTOs;

readonly class RowDto
{
    public function __construct(
        public int   $faixaEtariaId,
        public float $valorApartamento,
        public float $valorEnfermaria,
        public float $valorAmbulatorial = 0.0,
    ) {}
}
