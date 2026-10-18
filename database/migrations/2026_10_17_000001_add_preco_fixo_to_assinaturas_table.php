<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assinaturas', function (Blueprint $table) {
            // Preço negociado na mão com o cliente: o recálculo por usuários
            // ativos (toggle e assinaturas:recalcular-ativos) atualiza só a
            // contagem e nunca mexe no preco_total.
            $table->boolean('preco_fixo')->default(false)->after('preco_total');
        });
    }

    public function down(): void
    {
        Schema::table('assinaturas', function (Blueprint $table) {
            $table->dropColumn('preco_fixo');
        });
    }
};
