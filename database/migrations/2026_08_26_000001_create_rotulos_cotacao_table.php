<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rótulos personalizados da cotação Hapvida (pedido dos vendedores):
 * o nome do plano no cabeçalho ("COLETIVO POR ADESÃO" -> "ADESÃO"), o título
 * "COM COPARTICIPAÇÃO" e o título "COM COPART PARCIAL * / SEM COPARTICIPAÇÃO *".
 *
 * Cascata de resolução: usuário > assinatura (escritório) > padrão do sistema.
 * Cada linha tem user_id OU assinatura_id. plano_id só na chave nome_plano.
 * Editado só pelo desenvolvedor, na aba Rótulos de /configuracoes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rotulos_cotacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('assinatura_id')->nullable()->constrained('assinaturas')->cascadeOnDelete();
            $table->foreignId('plano_id')->nullable()->constrained('planos')->cascadeOnDelete();
            $table->enum('chave', ['nome_plano', 'com_copart', 'copart_parcial']);
            $table->string('texto', 40);
            $table->timestamps();

            $table->index(['user_id', 'chave']);
            $table->index(['assinatura_id', 'chave']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rotulos_cotacao');
    }
};
