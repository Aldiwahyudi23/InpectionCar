<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Console\Scheduling\Schedule as SchedulingSchedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
        // 'role' => \App\Http\Middleware\CheckRole::class,
         // Spatie
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

        'role_spatie' => \App\Http\Middleware\CheckSpatieRole::class,
        'is_admin' => \App\Http\Middleware\EnsureIsAdmin::class,
          // middleware custom kamu
        'region.active' => \App\Http\Middleware\CheckRegionMembership::class,
        'user.active' => \App\Http\Middleware\CheckUserStatus::class,
        
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
         // 🔹 Handle AuthorizationException (spatie permission / policy)
        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            return redirect()->route('error.403');
        });

        // 🔹 Handle HttpException 403
        $exceptions->render(function (HttpException $e, $request) {
            if ($e->getStatusCode() === 403) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Forbidden'], 403);
                }

                return redirect()->route('error.403');
            }
        });
    })
    ->withSchedule(function (SchedulingSchedule $schedule) {
           $schedule->command('inspection:cleanup')->daily(); // jalan tiap hari
        //    $schedule->command('inspection:cleanup')->hourly();
            // $schedule->command('inspection:cleanup')->everyMinute();
    })
    ->create();
