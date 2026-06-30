<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks unverified société owners — but only when e-mail verification is
 * enabled (config saas.email_verification). While it stays off (mail server
 * not ready), this middleware is a transparent pass-through.
 */
class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('saas.email_verification')) {
            return $next($request);
        }

        $user = $request->user();

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail() && ! $user->is_super_admin) {
            return $request->expectsJson()
                ? abort(409, 'Adresse e-mail non vérifiée.')
                : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
