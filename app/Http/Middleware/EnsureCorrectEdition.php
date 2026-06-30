<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hard guarantee that a deployment runs the edition it is supposed to.
 *
 * The branch bakes in config('saas.edition') ('saas' here, 'standalone' on
 * main). The server declares what it expects through APP_EDITION in .env. If
 * they disagree, the app refuses to serve any HTTP request — so the SaaS build
 * can never be served on a single-client server, nor the standalone build on
 * the SaaS platform, even if the wrong branch is checked out by mistake.
 *
 * Console commands (migrations, key:generate, ...) are unaffected, so a
 * mis-deployed server can still be fixed from the CLI.
 */
class EnsureCorrectEdition
{
    public function handle(Request $request, Closure $next): Response
    {
        $built = config('saas.edition');
        $expected = config('saas.expected_edition');

        if ($expected !== $built) {
            abort(503, sprintf(
                "Édition incorrecte : ce code est l'édition « %s » mais APP_EDITION=%s. ".
                'Définissez APP_EDITION=%s dans le .env de cet environnement (ou déployez la bonne branche).',
                $built,
                $expected === null ? '(absent)' : $expected,
                $built,
            ));
        }

        return $next($request);
    }
}
