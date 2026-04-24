<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_sessions', function (Blueprint $table) {
            $table->string('canvas_hash', 16)->nullable()->after('screen_resolution');
            $table->string('gpu_renderer', 255)->nullable()->after('canvas_hash');
            $table->tinyInteger('cpu_cores')->unsigned()->nullable()->after('gpu_renderer');
            $table->tinyInteger('device_memory')->unsigned()->nullable()->after('cpu_cores');
        });
    }

    public function down(): void
    {
        Schema::table('login_sessions', function (Blueprint $table) {
            $table->dropColumn(['canvas_hash', 'gpu_renderer', 'cpu_cores', 'device_memory']);
        });
    }
};
