<?php

use App\Http\Middleware\EnsureBiodataComplete;
use App\Http\Middleware\EnsureEmailVerifiedExceptSuperadmin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\Superadmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'superadmin' => Superadmin::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,

            'biodata.complete' => EnsureBiodataComplete::class,
            'email.verified.except.superadmin' => EnsureEmailVerifiedExceptSuperadmin::class,
        ]);

        if (env('REDIS_THROTTLE', false)) {
            $middleware->throttleWithRedis();
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
