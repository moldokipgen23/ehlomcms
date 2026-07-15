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
        // Bunny's CDN/edge sits in front of the container — trust its
        // forwarded headers so Laravel sees the real client IP and scheme.
        $middleware->trustProxies(at: '*');

        $middleware->prepend(\App\Http\Middleware\ResolveTenant::class);

        $middleware->alias([
            'tenant' => \App\Http\Middleware\EnsureTenantResolved::class,
            'tenant.auth' => \App\Http\Middleware\TenantAuthenticate::class,
            'admin.role' => \App\Http\Middleware\AdminRole::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
