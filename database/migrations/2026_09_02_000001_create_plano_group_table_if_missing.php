<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * plano_group e planos.plano_group_id foram criados À MÃO no banco (produção e
 * espelho local) e nunca tiveram migration — o banco de testes (sqlite) quebrava
 * em qualquer query do dashboard. Tudo aqui é guardado com hasTable/hasColumn:
 * onde a estrutura já existe, esta migration não faz NADA.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plano_group')) {
            Schema::create('plano_group', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('planos', 'plano_group_id')) {
            Schema::table('planos', function (Blueprint $table) {
                $table->unsignedBigInteger('plano_group_id')->nullable()->after('nome');
            });
        }
    }

    public function down(): void
    {
        // Não derruba nada: em produção a estrutura pré-existe fora de migration.
    }
};
