<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MODIFY/ENUM é MySQL puro — no sqlite dos testes a coluna já nasce como
        // string, então não há o que alterar.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE pix_pendentes MODIFY COLUMN tipo ENUM('new_user', 'renewal', 'team_user') NOT NULL DEFAULT 'new_user'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE pix_pendentes MODIFY COLUMN tipo ENUM('new_user', 'renewal') NOT NULL DEFAULT 'new_user'");
    }
};
