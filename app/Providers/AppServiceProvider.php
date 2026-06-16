<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureCodespaceUrl();

        // Admins ("gérant") bypass every gate.
        Gate::before(function (User $user) {
            return $user->is_admin ? true : null;
        });

        // Register a gate for each catalogued permission.
        foreach (Permissions::all() as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }

        // Expose company settings to every view (logo, name, ...).
        View::composer('*', function ($view) {
            if (! $view->offsetExists('appSettings')) {
                $view->with('appSettings', $this->settings());
            }
        });
    }

    /**
     * When running inside a GitHub Codespace, the forwarded request arrives with
     * a "localhost" Host header, so redirects (login, etc.) would point to
     * localhost. Force the public Codespaces URL instead — zero config needed.
     */
    private function configureCodespaceUrl(): void
    {
        $codespace = $_SERVER['CODESPACE_NAME'] ?? getenv('CODESPACE_NAME') ?: null;
        $domain = $_SERVER['GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN']
            ?? getenv('GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN') ?: null;

        if ($codespace && $domain) {
            URL::forceRootUrl("https://{$codespace}-8000.{$domain}");
            URL::forceScheme('https');
        }
    }

    /** @return array<string, string|null> */
    private function settings(): array
    {
        try {
            return Setting::all();
        } catch (\Throwable) {
            return []; // DB not migrated yet (e.g. during install)
        }
    }
}
