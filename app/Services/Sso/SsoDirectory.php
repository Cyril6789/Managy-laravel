<?php

namespace App\Services\Sso;

use App\Models\Setting;
use App\Models\Society;
use App\Models\SsoConnection;
use App\Support\Tenancy;
use Illuminate\Support\Collection;

/**
 * Resolves SSO connections and per-société login methods for the login page.
 *
 * Runs in a guest context, where the société global scope is inactive, so it
 * legitimately sees every space's connection to route a visitor to the right one.
 */
class SsoDirectory
{
    public function __construct(private readonly Tenancy $tenancy) {}

    /** Enabled + usable connections for a provider. */
    public function enabledConnections(string $provider): Collection
    {
        return SsoConnection::query()
            ->where('provider', $provider)
            ->where('enabled', true)
            ->get()
            ->filter(fn (SsoConnection $c) => $c->isUsable())
            ->values();
    }

    /**
     * Enabled + usable connections (any provider) whose domain accepts the e-mail,
     * most specific first (an explicit domain wins over a catch-all).
     *
     * @return Collection<int, SsoConnection>
     */
    public function connectionsForEmail(string $email): Collection
    {
        return SsoConnection::query()
            ->where('enabled', true)
            ->get()
            ->filter(fn (SsoConnection $c) => $c->isUsable() && $c->acceptsEmail($email))
            ->sortByDesc(fn (SsoConnection $c) => $c->domains() === [] ? 0 : 1)
            ->values();
    }

    /** Best connection for a given provider matching the e-mail domain. */
    public function connectionForEmail(string $provider, string $email): ?SsoConnection
    {
        return $this->enabledConnections($provider)
            ->filter(fn (SsoConnection $c) => $c->acceptsEmail($email))
            ->sortByDesc(fn (SsoConnection $c) => $c->domains() === [] ? 0 : 1)
            ->first();
    }

    /** The enabled + usable connection of a société for a provider, if any. */
    public function connectionFor(int $societyId, string $provider): ?SsoConnection
    {
        return SsoConnection::query()
            ->where('society_id', $societyId)
            ->where('provider', $provider)
            ->where('enabled', true)
            ->get()
            ->first(fn (SsoConnection $c) => $c->isUsable());
    }

    /** Whether password login is offered for a société (the gérant always keeps it). */
    public function passwordAllowed(?int $societyId): bool
    {
        if ($societyId === null) {
            return true;
        }

        return (bool) $this->tenancy->forSociety(
            $societyId,
            fn () => Setting::get('login_password_enabled', true),
        );
    }

    /**
     * Login methods offered on a société's dedicated page.
     *
     * @return array{password: bool, microsoft: bool, google: bool}
     */
    public function methodsFor(Society $society): array
    {
        return [
            'password' => $this->passwordAllowed($society->id),
            'microsoft' => $this->connectionFor($society->id, SsoConnection::PROVIDER_MICROSOFT) !== null,
            'google' => $this->connectionFor($society->id, SsoConnection::PROVIDER_GOOGLE) !== null,
        ];
    }
}
