<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_id', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_fingerprint', 32)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('os', 100)->nullable();
            $table->boolean('is_mobile')->default(false);
            $table->timestamp('logged_in_at')->useCurrent();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('logged_out_at')->nullable();
            $table->boolean('was_displaced')->default(false); // deslogado por novo acesso

            $table->index('user_id');
            $table->index('device_fingerprint');
            $table->index('logged_in_at');
            $table->index(['user_id', 'logged_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_sessions');
    }
};
