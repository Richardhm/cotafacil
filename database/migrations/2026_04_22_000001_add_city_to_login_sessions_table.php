<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_sessions', function (Blueprint $table) {
            $table->string('city', 100)->nullable()->after('ip_address');
            $table->string('country', 100)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('login_sessions', function (Blueprint $table) {
            $table->dropColumn(['city', 'country']);
        });
    }
};
