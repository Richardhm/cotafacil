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
        // Em produção o app roda atrás de um proxy na porta 8080, então forçamos o
        // root/scheme para as URLs geradas (route(), url(), asset()) ficarem corretas.
        // Em ambiente local isso geraria URLs de produção e quebraria as chamadas AJAX
        // com erro de CORS, por isso só aplicamos fora do ambiente local.
        if (! $this->app->environment('local')) {
            \URL::forceRootUrl('http://179.197.70.72:8080');
            \URL::forceScheme('http');
        }

        // Garante que o diretório de fontes do dompdf (font_dir/font_cache) exista.
        // O dompdf grava aqui o installed-fonts.json e o cache das fontes, mas NÃO
        // cria o diretório sozinho — sem isso, a geração de cotação falha em produção.
        $fontDir = storage_path('fonts');
        if (! is_dir($fontDir)) {
            @mkdir($fontDir, 0775, true);
        }
    }
}
