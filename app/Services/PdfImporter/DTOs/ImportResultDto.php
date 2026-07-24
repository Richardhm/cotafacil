<?php

namespace App\Services\PdfImporter\DTOs;

class ImportResultDto
{
    public int   $cidadesEncontradas = 0;
    public int   $tabelasAtualizadas = 0;
    public int   $tabelasCadastradas = 0;
    public int   $registrosGravados  = 0;
    public array $erros    = [];
    public array $avisos   = [];
    public array $detalhes = [];

    /** Linhas VALUES para o bloco INSERT (todos os registros). */
    public array $sqlInsertRows = [];

    /** Statements individuais para o bloco UPDATE (todos os registros). */
    public array $sqlUpdateRows = [];

    public function addErro(string $msg): void    { $this->erros[]    = $msg; }
    public function addAviso(string $msg): void   { $this->avisos[]   = $msg; }
    public function addDetalhe(string $msg): void { $this->detalhes[] = $msg; }

    public function temErros(): bool { return count($this->erros) > 0; }

    /** Retorna o bloco INSERT completo pronto para colar no servidor. */
    public function getSqlInsert(): string
    {
        if (empty($this->sqlInsertRows)) {
            return '';
        }
        $cols = '`administradora_id`, `tabela_origens_id`, `plano_id`, `acomodacao_id`, `faixa_etaria_id`, `coparticipacao`, `odonto`, `valor`, `created_at`, `updated_at`';
        $vals = implode(",\n", $this->sqlInsertRows);
        return "INSERT INTO `tabelas` ({$cols}) VALUES\n{$vals};";
    }

    /** Retorna o bloco UPDATE completo pronto para colar no servidor. */
    public function getSqlUpdate(): string
    {
        if (empty($this->sqlUpdateRows)) {
            return '';
        }
        return implode("\n", $this->sqlUpdateRows);
    }
}
