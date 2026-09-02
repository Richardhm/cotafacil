<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Caso Porto Alegre: o Super Simples ambulatorial tem coparticipações (tabela
 * pdf) DIFERENTES do Super Simples normal. A linha com ambulatorial=1 vale só
 * para a cotação ambulatorial; a cotação normal usa (e sempre usou) a linha 0.
 * Quem busca faz fallback: sem linha ambulatorial cadastrada, vale a normal.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pdf', 'ambulatorial')) {
            Schema::table('pdf', function (Blueprint $table) {
                $table->tinyInteger('ambulatorial')->default(0)->after('tabela_origens_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pdf', 'ambulatorial')) {
            Schema::table('pdf', function (Blueprint $table) {
                $table->dropColumn('ambulatorial');
            });
        }
    }
};
