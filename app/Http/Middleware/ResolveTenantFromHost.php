<?php

namespace App\Http\Middleware;

use App\Models\Society;
use App\Support\Tenancy;
use App\Support\TenantUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromHost
{
    public function __construct(private readonly Tenancy $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        $baseDomain = strtolower(trim((string) config('saas.domain')));
        $host = strtolower($request->getHost());

        if ($baseDomain === '' || ($host !== $baseDomain && ! str_ends_with($host, '.'.$baseDomain))) {
            return $next($request);
        }

        if ($host === $baseDomain) {
            $user = $request->user();

            // OAuth callbacks and signed verification links may deliberately use
            // the central host. Once completed, any regular authenticated request
            // is moved back to the user's own tenant while preserving its path.
            if ($user && ! $user->is_super_admin && ! $request->is('email/verify*')) {
                $society = Society::find($user->society_id);

                if ($society) {
                    $target = '/'.ltrim($request->getRequestUri(), '/');

                    return redirect()->away(TenantUrl::forSociety($society, $target));
                }
            }

            return $next($request);
        }

        $slug = substr($host, 0, -strlen('.'.$baseDomain));

        if ($slug === '' || str_contains($slug, '.') || in_array($slug, config('saas.reserved_subdomains', []), true)) {
            abort(404);
        }

        $society = Society::query()->where('slug', $slug)->firstOrFail();

        if (! $society->is_active) {
            abort(403, __('Cette société est désactivée.'));
        }

        $this->tenancy->set($society->id);
        $request->attributes->set('tenant', $society);

        $user = $request->user();

        if ($user?->is_super_admin) {
            return redirect()->away(TenantUrl::central('/admin'));
        }

        if ($user && (int) $user->society_id !== (int) $society->id) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->away(TenantUrl::forSociety($society, '/login'))
                ->withErrors(['email' => __('Votre session ne correspond pas à cette société.')]);
        }

        return $next($request);
    }
}
