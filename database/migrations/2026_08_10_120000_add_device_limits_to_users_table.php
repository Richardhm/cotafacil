<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Limite de dispositivos por usuário.
 *
 * Até aqui o limite era "1 desktop + 1 celular" fixo no código, e liberar um
 * aparelho a mais exigia UPDATE na mão em user_devices. Com estas colunas o
 * limite passa a ser um dado da conta.
 *
 * Default 1 nas duas: o comportamento continua EXATAMENTE o de hoje para todo
 * mundo até alguém mudar o valor de uma conta específica.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_desktops')->default(1)->after('imagem');
            $table->unsignedTinyInteger('max_mobiles')->default(1)->after('max_desktops');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['max_desktops', 'max_mobiles']);
        });
    }
};
