<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Decisão do Richard pós-deploy (19/08): o acesso ao módulo Humana é POR
 * USUÁRIO, não por assinatura — liberar o titular não pode liberar a equipe.
 *
 * A tabela é recriada (drop + create) em vez de alterada: ela nasceu hoje e
 * está vazia/quase vazia, e recriar funciona igual no MySQL de produção e no
 * sqlite dos testes (que não sabe dropar foreign key). Perde-se apenas alguma
 * liberação de teste feita hoje.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('humana_acessos');

        Schema::create('humana_acessos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('humana_acessos');

        Schema::create('humana_acessos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assinatura_id')->unique()->constrained('assinaturas');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }
};
