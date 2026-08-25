<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Apelido do plano para o cabeçalho da 1ª coluna das cotações (era o texto
 * fixo "NOSSO PLANO"). Nullable de propósito: enquanto não for preenchido,
 * a cotação continua saindo "NOSSO PLANO" — nada muda até o Richard preencher
 * plano por plano (Individual, Super Simples, Coletivo...).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->string('apelido', 40)->nullable()->after('nome');
        });
    }

    public function down(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->dropColumn('apelido');
        });
    }
};
