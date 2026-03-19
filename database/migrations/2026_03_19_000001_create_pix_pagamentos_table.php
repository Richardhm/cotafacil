<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pix_pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('assinatura_id')->constrained()->onDelete('cascade');
            $table->string('txid')->unique();
            $table->decimal('valor', 10, 2);
            $table->string('tipo')->default('PIX'); // PIX ou PIX_AUTOMATICO
            $table->timestamp('pago_em')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pix_pagamentos');
    }
};
