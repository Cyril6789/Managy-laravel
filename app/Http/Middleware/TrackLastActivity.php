<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackLastActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Throttle writes to at most once per minute.
            if (! $user->last_action_at || $user->last_action_at->diffInSeconds(now()) > 60) {
                $user->forceFill(['last_action_at' => now()])->saveQuietly();
            }
        }

        return $next($request);
    }
}
