<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pix_pendentes', function (Blueprint $table) {
            // Valor e assinatura da renovação, para o webhook liberar sem depender do
            // navegador (o polling deixava isso a cargo do request do cliente).
            $table->decimal('valor', 10, 2)->nullable()->after('tipo');
            $table->foreignId('assinatura_id')->nullable()->after('user_id')
                ->constrained('assinaturas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pix_pendentes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assinatura_id');
            $table->dropColumn('valor');
        });
    }
};
