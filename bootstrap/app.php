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
    ->withMiddleware(function (Illuminate\Foundation\Configuration\Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'set.locale' => \App\Http\Middleware\SetLocale::class,
            'api.token' => \App\Http\Middleware\ApiTokenMiddleware::class,
            'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'memory.monitor' => \App\Http\Middleware\MemoryMonitorMiddleware::class,
            'request.size.limit' => \App\Http\Middleware\RequestSizeLimitMiddleware::class,
        ]);

        // Apply global middleware
        $middleware->append(\App\Http\Middleware\MemoryMonitorMiddleware::class);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
