<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'complete.registration' => \App\Http\Middleware\CompleteRegistration::class,
            'single.session' => \App\Http\Middleware\SingleSession::class,
        ]);

        // Add SingleSession middleware to web group
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SingleSession::class,
            \App\Http\Middleware\DetectCountry::class,
            \App\Http\Middleware\GeoIpCountry::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
