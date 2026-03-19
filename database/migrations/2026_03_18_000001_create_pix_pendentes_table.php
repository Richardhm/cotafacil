<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pix_pendentes', function (Blueprint $table) {
            $table->id();
            $table->string('txid', 255)->unique();
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->enum('tipo', ['new_user', 'renewal'])->default('new_user');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pix_pendentes');
    }
};
