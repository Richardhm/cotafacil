<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo Humana (Teresina-PI) — schema completo e ISOLADO da Hapvida.
 *
 * Hierarquia: humana_planos (linha comercial) -> humana_tabelas (uma por página
 * de preço do PDF: acomodação × coparticipação) -> humana_precos (uma por faixa
 * etária, com os 3 valores em colunas: Saúde / Combo Essencial / Combo Pleno).
 *
 * Combinação inválida (ex.: VITAL PME sem obstetrícia) simplesmente não tem
 * linha — o front monta os filtros consultando o que existe, sem if/else.
 *
 * Uma migration só de propósito: no deploy é um único
 * php artisan migrate --path=database/migrations/2026_08_19_000001_create_humana_tables.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('humana_faixa_etarias', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 20);                       // "00 a 18" ... "59 ou mais"
            $table->unsignedTinyInteger('idade_min');
            $table->unsignedTinyInteger('idade_max')->nullable(); // null = sem teto (59+)
            $table->timestamps();
        });

        Schema::create('humana_planos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 60);                       // exibição: "VITAL — Sem Obstetrícia"
            $table->enum('contratacao', ['pf', 'pme']);
            $table->string('linha', 30);                      // VITAL, IDEAL, SUPERIOR, SUPERIOR R2, ...
            $table->boolean('obstetricia')->nullable();       // null = não se aplica (Ambulatorial PME)
            $table->string('segmentacao', 60);
            $table->string('abrangencia', 120)->nullable();   // Teresina/Timon, Grupo de Municípios, Nacional
            $table->unsignedTinyInteger('ordem')->default(0); // ordem dos cards na tela
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['contratacao', 'linha', 'obstetricia']);
        });

        Schema::create('humana_tabelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('humana_plano_id')->constrained('humana_planos');
            // PME chama de Coletiva/Individual, mas é o mesmo conceito (QC/QP);
            // o rótulo certo por contratação é responsabilidade da view.
            $table->enum('acomodacao', ['enfermaria', 'apartamento', 'nenhuma']);
            $table->enum('coparticipacao', ['completa', 'basica', 'nao_se_aplica']);
            $table->string('registro_ans', 20)->nullable();
            $table->date('vigencia_inicio')->nullable();
            $table->date('vigencia_fim')->nullable();
            $table->timestamps();

            $table->unique(['humana_plano_id', 'acomodacao', 'coparticipacao'], 'humana_tabelas_combinacao_unique');
        });

        Schema::create('humana_precos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('humana_tabela_id')->constrained('humana_tabelas');
            $table->foreignId('humana_faixa_etaria_id')->constrained('humana_faixa_etarias');
            $table->decimal('valor_saude', 8, 2);
            // Combos nullable: nas REFERÊNCIA o PDF traz N/A.
            $table->decimal('valor_combo_essencial', 8, 2)->nullable();
            $table->decimal('valor_combo_pleno', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['humana_tabela_id', 'humana_faixa_etaria_id'], 'humana_precos_tabela_faixa_unique');
        });

        Schema::create('humana_acessos', function (Blueprint $table) {
            $table->id();
            // Libera a conta inteira (titular + equipe), como todo vínculo do sistema.
            $table->foreignId('assinatura_id')->unique()->constrained('assinaturas');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('humana_acessos');
        Schema::dropIfExists('humana_precos');
        Schema::dropIfExists('humana_tabelas');
        Schema::dropIfExists('humana_planos');
        Schema::dropIfExists('humana_faixa_etarias');
    }
};
