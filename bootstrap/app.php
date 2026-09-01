<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'branch.selected' => \App\Http\Middleware\EnsureBranchIsSelected::class,
                    'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        'bot.token' => \App\Http\Middleware\VerifyBotApiToken::class,

    ]);
})

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

    