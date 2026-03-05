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
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'platform_staff' => \App\Http\Middleware\EnsureSuperAdminOrSupport::class,
            'company_operational' => \App\Http\Middleware\EnsureCompanyOperationalAccess::class,
            'company_access' => \App\Http\Middleware\EnsureCanAccessCompany::class,
            'company_admin_only' => \App\Http\Middleware\EnsureCompanyAdminOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
