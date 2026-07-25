<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Troca a FK de tabela_origens_id de ON DELETE SET NULL para ON DELETE CASCADE.
     *
     * Com SET NULL, excluir uma cidade não apagava os vínculos: apenas zerava o
     * tabela_origens_id, deixando linhas sem cidade que nunca casam com nenhuma
     * consulta (o dashboard sempre filtra por tabela_origens_id). Isso já havia
     * acumulado 982 linhas mortas.
     */
    public function up(): void
    {
        // Sobrou alguma linha órfã de antes da troca? Ela impediria o CASCADE de fazer sentido.
        DB::table('administradora_planos')->whereNull('tabela_origens_id')->delete();

        Schema::table('administradora_planos', function (Blueprint $table) {
            $table->dropForeign(['tabela_origens_id']);
            $table->foreign('tabela_origens_id')
                ->references('id')
                ->on('tabela_origens')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('administradora_planos', function (Blueprint $table) {
            $table->dropForeign(['tabela_origens_id']);
            $table->foreign('tabela_origens_id')
                ->references('id')
                ->on('tabela_origens')
                ->onDelete('set null');
        });
    }
};
