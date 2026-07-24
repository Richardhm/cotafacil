<?php

namespace App\Services\ImportacaoAssistida\DTOs;

class RegistroDto
{
    public ?float $valorDb = null;
    public ?int   $dbId    = null;
    public string $status  = 'inserir'; // inserir | atualizar | igual

    public function __construct(
        public readonly int   $planoId,
        public readonly int   $acomodacaoId,
        public readonly int   $faixaEtariaId,
        public readonly int   $coparticipacao,
        public readonly int   $odonto,
        public readonly float $valorPdf,
    ) {}
}
