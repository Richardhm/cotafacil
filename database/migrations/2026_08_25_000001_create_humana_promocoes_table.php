<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Promoções da Humana exibidas nas cotações (tela e documentos).
 * Regra casa por contratação + coparticipação; NULL = vale para todas.
 * Promoção muda todo mês — é dado, não código: desativar/trocar o texto
 * não pede deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('humana_promocoes', function (Blueprint $table) {
            $table->id();
            $table->enum('contratacao', ['pf', 'pme'])->nullable();      // null = ambas
            $table->enum('coparticipacao', ['completa', 'basica'])->nullable(); // null = ambas
            $table->string('texto', 160);                 // frase da faixa laranja
            // Desconto calculável (opcional): gera a linha "com desconto" abaixo
            // do TOTAL, como na cotação Hapvida. null = promoção só textual.
            $table->unsignedTinyInteger('pct_desconto')->nullable(); // 0-100
            $table->string('rotulo', 40)->nullable();                // selo da linha: "Des. 30% 6/meses"
            $table->boolean('ativo')->default(true);
            $table->unsignedTinyInteger('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('humana_promocoes');
    }
};
