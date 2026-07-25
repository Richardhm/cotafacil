<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índice único em (assinatura_id, administradora_id, plano_id, tabela_origens_id).
     *
     * Sem ele a mesma combinação entrava várias vezes sem erro nenhum — foi assim que
     * "Qualicorp / Coletivo por Adesão / Goiânia" acabou duplicado em 29 assinaturas,
     * aparecendo duas vezes na listagem de vínculos.
     *
     * Atenção: no MySQL, NULL não conflita com NULL num índice único. As 1.270 linhas
     * antigas com assinatura_id NULL continuam sem proteção — o índice só cobre linhas
     * com as quatro colunas preenchidas, que é o caso de tudo que o sistema cria hoje.
     */
    public function up(): void
    {
        Schema::table('administradora_planos', function (Blueprint $table) {
            $table->unique(
                ['assinatura_id', 'administradora_id', 'plano_id', 'tabela_origens_id'],
                'administradora_planos_unico'
            );
        });
    }

    public function down(): void
    {
        Schema::table('administradora_planos', function (Blueprint $table) {
            $table->dropUnique('administradora_planos_unico');
        });
    }
};
