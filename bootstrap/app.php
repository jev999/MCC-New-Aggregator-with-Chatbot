<?php

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
    ->withMiddleware(function (Middleware $middleware): void {
        // Register security middleware
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class, // Security headers (HSTS, CSP, etc.)
            \App\Http\Middleware\ForceHttps::class,       // HTTPS enforcement
            \App\Http\Middleware\MonitoringMiddleware::class, // Performance monitoring
        ]);
        
        // Basic middleware configuration
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
