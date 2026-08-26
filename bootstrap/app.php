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
        // Mendaftarkan alias middleware role kita
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Menambahkan middleware pelacak aktivitas secara otomatis pada grup web
        $middleware->appendToGroup('web', \App\Http\Middleware\LogUserActivity::class);

        // Kecualikan route callback Midtrans dari verifikasi CSRF.
        // Midtrans mengirim POST dari server mereka sehingga tidak memiliki
        // CSRF token dari session browser. Tanpa pengecualian ini, seluruh
        // callback akan ditolak dengan HTTP 419 (CSRF Token Mismatch).
        $middleware->validateCsrfTokens(except: [
            'payment/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();