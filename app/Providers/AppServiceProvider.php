<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Em produção o app roda atrás de um proxy (Nginx) na porta 8080. O host e o
        // scheme corretos vêm dos headers X-Forwarded-Host/X-Forwarded-Proto, confiados
        // via trustProxies() no bootstrap/app.php.
        //
        // Não voltar a usar URL::forceRootUrl() aqui: além de duplicar o que o
        // trustProxies já resolve, um root URL fixo vaza para as respostas da API do app
        // mobile (apimobile.bmsys.com.br), que geraria links para o host errado.

        // Garante que o diretório de fontes do dompdf (font_dir/font_cache) exista.
        // O dompdf grava aqui o installed-fonts.json e o cache das fontes, mas NÃO
        // cria o diretório sozinho — sem isso, a geração de cotação falha em produção.
        $fontDir = storage_path('fonts');
        if (! is_dir($fontDir)) {
            @mkdir($fontDir, 0775, true);
        }
    }
}
