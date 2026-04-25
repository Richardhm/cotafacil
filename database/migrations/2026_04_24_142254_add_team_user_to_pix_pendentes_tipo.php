<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pix_pendentes MODIFY COLUMN tipo ENUM('new_user', 'renewal', 'team_user') NOT NULL DEFAULT 'new_user'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pix_pendentes MODIFY COLUMN tipo ENUM('new_user', 'renewal') NOT NULL DEFAULT 'new_user'");
    }
};
