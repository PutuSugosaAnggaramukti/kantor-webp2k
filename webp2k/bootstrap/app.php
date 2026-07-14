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
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '/user/kunjungan/store',
            '/user/kunjungan/tambah-jadwal',
            '/user/kunjungan/update-jadwal-global',
            '/admin/datakunjungan/store',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
