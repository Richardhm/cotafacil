<?php

namespace App\Services\ImportacaoAssistida\DTOs;

class CidadeResultDto
{
    public int    $totalInserir   = 0;
    public int    $totalAtualizar = 0;
    public int    $totalIgual     = 0;
    public array  $avisos         = [];
    public string $sqlInsert      = '';
    public string $sqlUpdate      = '';

    /**
     * @param RegistroDto[] $registros
     */
    public function __construct(
        public readonly string $cidade,
        public readonly string $uf,
        public readonly int    $administradoraId,
        public readonly ?int   $cidadeId,
        public readonly bool   $cidadeCriada,
        public array           $registros,
    ) {}

    public function addAviso(string $msg): void
    {
        $this->avisos[] = $msg;
    }
}
