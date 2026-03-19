<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pix_pendentes', function (Blueprint $table) {
            $table->string('id_rec', 100)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('pix_pendentes', function (Blueprint $table) {
            $table->dropColumn('id_rec');
        });
    }
};
