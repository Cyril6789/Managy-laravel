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
        $this->configureMailFromSettings();

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
     * Configure the SMTP mailer from the in-app settings (Paramètres → E-mail),
     * so each instance can use its own mail server without editing .env.
     */
    private function configureMailFromSettings(): void
    {
        try {
            $s = $this->settings();
        } catch (\Throwable) {
            return;
        }

        if (empty($s['mail_host'])) {
            return;
        }

        $encryption = ($s['mail_encryption'] ?? 'tls');
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $s['mail_host'],
            'mail.mailers.smtp.port' => (int) ($s['mail_port'] ?? 587),
            'mail.mailers.smtp.username' => $s['mail_username'] ?: null,
            'mail.mailers.smtp.password' => $s['mail_password'] ?: null,
            'mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption,
        ]);

        if (! empty($s['mail_from_address'])) {
            config([
                'mail.from.address' => $s['mail_from_address'],
                'mail.from.name' => $s['mail_from_name'] ?: ($s['company_name'] ?? config('app.name')),
            ]);
        }
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
