<?php

use App\Http\Middleware\EnsureCorrectEdition;
use App\Http\Middleware\ResolveTenantFromHost;
use App\Http\Middleware\TrackLastActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust Traefik / reverse proxies so Laravel detects the original HTTPS
        // request and generates secure tenant URLs and cookies.
        $middleware->trustProxies(at: '*');

        $middleware->web(prepend: [
            // Refuse to serve if the deployed branch (saas) doesn't match the
            // environment's declared APP_EDITION. Runs before everything else.
            EnsureCorrectEdition::class,
        ], append: [
            // Resolve {slug}.managy.fr after the session middleware is available,
            // but before controllers and tenant-scoped models are executed.
            ResolveTenantFromHost::class,
            TrackLastActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
