<?php

use App\Http\Middleware\ApplyBranding;
use App\Http\Middleware\EnsureInstalled;
use App\Http\Middleware\RequireRole;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// An unfinished installation must not boot with a stale cached database or key.
if (! is_file(dirname(__DIR__).'/storage/installed.lock') && ! isset($_ENV['PHPUNIT_COMPOSER_INSTALL'])) {
    $_ENV['APP_CONFIG_CACHE'] = $_SERVER['APP_CONFIG_CACHE'] = dirname(__DIR__).'/storage/framework/install.config.php';
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'installed' => EnsureInstalled::class,
            'role' => RequireRole::class,
        ]);
        $middleware->web(append: [ApplyBranding::class]);
        $middleware->prependToPriorityList(AuthenticatesRequests::class, EnsureInstalled::class);
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['password', 'password_confirmation', 'db_password', 'mail_password']);
    })->create();
