<?php

use App\Http\Middleware\ApiTokenAuth;
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\RedirectIfNotManager;
use App\Http\Middleware\RedirectToGoogleLoginIfNotGoogleAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\TrimStrings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    // Listeners are registered explicitly in AppServiceProvider; discovery
    // would register them a second time and send every PTO email twice.
    ->withEvents(discover: false)
    ->withMiddleware(function (Middleware $middleware): void {
        // Laravel 6 ran neither of these globally. Forms post empty strings for
        // optional columns (phone, max_days_off, ...) and the models rely on it.
        $middleware->remove([
            ConvertEmptyStringsToNull::class,
            TrimStrings::class,
        ]);

        $middleware->throttleApi('60,1');

        $middleware->redirectUsersTo('/');

        $middleware->alias([
            'admin' => RedirectIfNotAdmin::class,
            'manager' => RedirectIfNotManager::class,
            'google' => RedirectToGoogleLoginIfNotGoogleAuth::class,
            'auth.api-token' => ApiTokenAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
