<?php

use App\Http\Middleware\EnsureCorrectEdition;
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
        // Trust the reverse proxy (Codespaces, Render, Fly, load balancers…) so
        // Laravel detects HTTPS and generates correct asset / cookie URLs.
        $middleware->trustProxies(at: '*');

        $middleware->web(prepend: [
            // Refuse to serve if the deployed branch (saas) doesn't match the
            // environment's declared APP_EDITION. Runs before everything else.
            EnsureCorrectEdition::class,
        ], append: [
            TrackLastActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
