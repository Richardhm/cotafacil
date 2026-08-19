<?php

use App\Http\Middleware\ApenasAdministrador;
use App\Http\Middleware\ApenasDesenvolvedor;
use App\Http\Middleware\CheckBlockedIp;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\CheckSubscriptionExpired;
use App\Http\Middleware\PreventSimultaneousLogins;
use App\Http\Middleware\SomentePagamento;
use App\Http\Middleware\TrackSessionActivity;
use App\Http\Middleware\MobileSessionFix;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // O app roda atrás do Nginx (proxy para a porta 8080). Sem isto o Laravel gera
        // URLs http:// e $request->ip() devolve o IP do proxy para TODOS os usuários,
        // contaminando device_fingerprint, login_sessions.ip_address e BlockedIp.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->validateCsrfTokens(except: [
            'login',
            'logout',
            '/callback',
            '/callback/*',
            '/pix/webhook',
            '/pix/webhook/pix',
            '/pix/webhook-rec',
        ]);
        $middleware->append(MobileSessionFix::class);
        $middleware->append(TrackSessionActivity::class);
        // SomentePagamento depois do CheckBlockedIp: só age quando a sessão tem a
        // marca 'modo_pagamento' (login de celular com ?pagamento=1) e a tranca nas
        // rotas de assinatura. Para todas as outras sessões é um no-op.
        $middleware->web(append: [CheckBlockedIp::class, SomentePagamento::class]);
        $middleware->alias([
            'prevent-simultaneous-logins' => PreventSimultaneousLogins::class,
            'apenasDesenvolvedores' => ApenasDesenvolvedor::class,
            'apenasAdministradores' => ApenasAdministrador::class,
            'check' => CheckSubscription::class,
            'checkExpired' => CheckSubscriptionExpired::class,
            'email.verified.afterDeadline' => \App\Http\Middleware\EnsureEmailIsVerifiedAfterDeadline::class,
            'humana' => \App\Http\Middleware\AcessoHumana::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
