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
    ->withMiddleware(function (Middleware $middleware) {
        // Add your custom middleware alias here
        $middleware->alias([
            'check.role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // If you have other middleware aliases, add them too
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
