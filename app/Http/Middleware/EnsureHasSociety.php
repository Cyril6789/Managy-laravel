<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the business application. The platform super-admin has no société, so
 * letting them in would disable the tenant scope and leak every société's data
 * into one screen — redirect them to their supervision area instead.
 */
class EnsureHasSociety
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_super_admin) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
