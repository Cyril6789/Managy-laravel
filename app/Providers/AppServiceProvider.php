<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
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
