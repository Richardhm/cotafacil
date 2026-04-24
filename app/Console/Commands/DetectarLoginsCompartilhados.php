<?php

namespace App\Console\Commands;

use App\Models\LoginSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DetectarLoginsCompartilhados extends Command
{
    protected $signature   = 'logins:detectar-compartilhados {--horas=48 : Janela de análise em horas}';
    protected $description = 'Identifica usuários com logins simultâneos em dispositivos distintos';

    public function handle(): int
    {
        $horas = (int) $this->option('horas');

        $this->info("Analisando últimas {$horas} horas...\n");

        $suspeitos = DB::table('login_sessions as ls')
            ->join('users', 'users.id', '=', 'ls.user_id')
            ->where('ls.logged_in_at', '>=', now()->subHours($horas))
            ->groupBy('ls.user_id', 'users.name', 'users.email')
            ->havingRaw('COUNT(DISTINCT ls.device_fingerprint) > 1')
            ->selectRaw('ls.user_id, users.name, users.email, COUNT(DISTINCT ls.device_fingerprint) as dispositivos_distintos, COUNT(*) as total_logins')
            ->orderByDesc('dispositivos_distintos')
            ->get();

        if ($suspeitos->isEmpty()) {
            $this->info('Nenhum login suspeito detectado.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Nome', 'E-mail', 'Dispositivos distintos', 'Total logins'],
            $suspeitos->map(fn($r) => [
                $r->user_id,
                $r->name,
                $r->email,
                $r->dispositivos_distintos,
                $r->total_logins,
            ])
        );

        $this->newLine();

        // Detalhe por usuário suspeito
        foreach ($suspeitos as $suspeito) {
            $this->line("<fg=yellow>Usuário: {$suspeito->name} ({$suspeito->email})</>");

            $sessoes = LoginSession::where('user_id', $suspeito->user_id)
                ->where('logged_in_at', '>=', now()->subHours($horas))
                ->orderByDesc('logged_in_at')
                ->get(['browser', 'os', 'ip_address', 'device_fingerprint', 'logged_in_at', 'last_seen_at', 'was_displaced']);

            foreach ($sessoes as $s) {
                $deslocado = $s->was_displaced ? ' [deslocado]' : '';
                $visto = $s->last_seen_at ? $s->last_seen_at->format('d/m H:i') : '—';
                $this->line("  • {$s->browser} / {$s->os} | IP: {$s->ip_address} | Login: {$s->logged_in_at->format('d/m H:i')} | Último acesso: {$visto}{$deslocado}");
            }

            $this->newLine();
        }

        return self::SUCCESS;
    }
}
