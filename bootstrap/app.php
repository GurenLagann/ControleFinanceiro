<?php

use App\Http\Middleware\CompressResponse;
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
        // Compressao de respostas HTTP
        $middleware->append(CompressResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
