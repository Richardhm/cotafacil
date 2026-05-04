<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_sessions', function (Blueprint $table) {
            $table->boolean('was_blocked')->default(false)->after('was_displaced');
        });
    }

    public function down(): void
    {
        Schema::table('login_sessions', function (Blueprint $table) {
            $table->dropColumn('was_blocked');
        });
    }
};
