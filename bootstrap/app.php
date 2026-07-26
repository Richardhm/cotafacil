<?php

use App\Http\Middleware\ApenasAdministrador;
use App\Http\Middleware\ApenasDesenvolvedor;
use App\Http\Middleware\CheckBlockedIp;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\CheckSubscriptionExpired;
use App\Http\Middleware\PreventSimultaneousLogins;
use App\Http\Middleware\TrackSessionActivity;
use App\Http\Middleware\MobileSessionFix;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
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
        $middleware->web(append: [CheckBlockedIp::class]);
        $middleware->alias([
            'prevent-simultaneous-logins' => PreventSimultaneousLogins::class,
            'apenasDesenvolvedores' => ApenasDesenvolvedor::class,
            'apenasAdministradores' => ApenasAdministrador::class,
            'check' => CheckSubscription::class,
            'checkExpired' => CheckSubscriptionExpired::class,
            'email.verified.afterDeadline' => \App\Http\Middleware\EnsureEmailIsVerifiedAfterDeadline::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
