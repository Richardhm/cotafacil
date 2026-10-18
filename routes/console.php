<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// E-mails automáticos DESATIVADOS a pedido do Richard (18/10/2026): nenhum
// cliente deve receber e-mail por enquanto. O aviso de vencimento do PIX fica
// por conta da barra no topo do sistema (layouts/alerta-vencimento). Reativar
// só depois de validar o envio de e-mail de produção com um destinatário nosso.
// app(Schedule::class)->command('email:send-reminder')->hourly();
// app(Schedule::class)->command('email:send-pending-verifications')->dailyAt('00:00');
// app(Schedule::class)->command('pix:alerta-vencimento')->dailyAt('08:00');

// 00:00/00:30 de propósito (pedido do Richard 18/10): se algo der errado,
// sobra a madrugada e a manhã inteira para corrigir antes do movimento.
// O timezone é obrigatório: a aplicação roda em UTC, e sem ele o "00:00"
// cairia às 21:00 de Brasília.
app(\Illuminate\Console\Scheduling\Schedule::class)
    ->command('cartao:sincronizar')
    ->dailyAt('00:00')
    ->timezone('America/Sao_Paulo');

// Reconcilia o valor das assinaturas de cartão na Efi com o preco_total do banco
// (rede de segurança para quando o espelhamento em tempo real falhar).
app(\Illuminate\Console\Scheduling\Schedule::class)
    ->command('cartao:corrigir-valores')
    ->dailyAt('00:30')
    ->timezone('America/Sao_Paulo');


