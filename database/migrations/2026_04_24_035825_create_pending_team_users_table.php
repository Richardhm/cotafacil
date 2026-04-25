<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pending_team_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assinatura_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('admin_user_id');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->decimal('valor', 8, 2);
            $table->boolean('inclui_ativacao_admin')->default(false);
            $table->string('txid')->nullable()->index();
            $table->string('payment_method')->default('pix'); // pix | cartao
            $table->string('status')->default('pending');     // pending | paid | expired
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['admin_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_team_users');
    }
};
