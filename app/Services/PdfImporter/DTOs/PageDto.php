<?php

namespace App\Services\PdfImporter\DTOs;

readonly class PageDto
{
    /**
     * @param TableDto[] $tables
     */
    public function __construct(
        public string $cidade,
        public string $uf,
        public string $administradora,
        public float  $valorPromoOdonto,
        public array  $tables,
        public int    $pageNumber,
    ) {}
}
