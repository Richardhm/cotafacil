<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Reset de dispositivos para migração de servidor.
 *
 * Problema: ao copiar o banco para o servidor novo, o user_devices de cada usuário
 * vem ATIVO. Como o limite é 1 desktop, a vaga já chega ocupada e quem loga no
 * servidor novo (nova origem = localStorage sem device_uuid) cai em
 * "Limite de computadores atingido".
 *
 * Solução: desativar todos os devices. No próximo login, cada usuário re-registra
 * o dispositivo atual dentro do limite. Preserva os is_blocked (bloqueios de admin).
 *
 * Rodar UMA vez no servidor novo, no cutover, ANTES de liberar os usuários:
 *   php artisan migracao:reset-dispositivos --force --truncate-sessions
 */
class ResetDispositivosMigracao extends Command
{
    protected $signature = 'migracao:reset-dispositivos
        {--force : Não pedir confirmação}
        {--truncate-sessions : Também limpar a tabela sessions (força todos a logar de novo)}';

    protected $description = 'Libera as vagas de dispositivo após migração de servidor (evita "Limite de computadores atingido").';

    public function handle(): int
    {
        $ativos = DB::table('user_devices')->where('is_active', true)->count();

        $this->line("Dispositivos ativos hoje: {$ativos}");
        $this->line('Ação: desativar todos (cada usuário re-registra no próximo login). Bloqueios de admin (is_blocked) são preservados.');

        if (! $this->option('force') && ! $this->confirm('Continuar?')) {
            $this->warn('Cancelado.');
            return self::SUCCESS;
        }

        // Sem updated_at aqui: user_devices não tem essa coluna (UserDevice usa
        // $timestamps = false). As colunas de tempo da tabela são registered_at e
        // last_used_at, e nenhuma das duas deve mudar num reset de vaga.
        DB::table('user_devices')->where('is_active', true)->update([
            'is_active' => false,
        ]);
        $this->info("Dispositivos desativados: {$ativos}");

        $sessoes = DB::table('login_sessions')->whereNull('logged_out_at')->update([
            'logged_out_at' => now(),
        ]);
        $this->info("Login sessions abertas encerradas: {$sessoes}");

        if ($this->option('truncate-sessions')) {
            DB::table('sessions')->truncate();
            $this->info('Tabela sessions limpa — todos os usuários vão logar de novo.');
        }

        $this->newLine();
        $this->info('Pronto. No próximo login, cada usuário registra o dispositivo atual dentro do limite de 1.');

        return self::SUCCESS;
    }
}
