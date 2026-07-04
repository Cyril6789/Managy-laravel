<?php

namespace App\Services\Sso;

use App\Models\SsoConnection;
use InvalidArgumentException;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;

/**
 * Builds a configured Socialite provider from a société's stored SSO credentials.
 *
 * Socialite reads its credentials from config('services.*'); since ours live in
 * the database (one set per société), we inject them into the config for the
 * duration of the request before resolving the driver.
 */
class SsoProviderFactory
{
    /** Managy provider slug => Socialite driver name. */
    public const DRIVERS = [
        SsoConnection::PROVIDER_MICROSOFT => 'azure',
        SsoConnection::PROVIDER_GOOGLE => 'google',
    ];

    public function make(SsoConnection $connection): Provider
    {
        $driver = self::DRIVERS[$connection->provider] ?? null;

        if ($driver === null) {
            throw new InvalidArgumentException("Unsupported SSO provider [{$connection->provider}].");
        }

        config([
            "services.{$driver}" => [
                'client_id' => $connection->client_id,
                'client_secret' => $connection->client_secret,
                'redirect' => route('sso.callback', ['provider' => $connection->provider]),
                'tenant' => $connection->tenant_id ?: 'common',
            ],
        ]);

        $provider = Socialite::driver($driver);

        if ($driver === 'azure') {
            // openid/profile/email identify the user; the Graph scopes let us read
            // their security-group memberships to drive the group synchronisation.
            $provider->scopes(['openid', 'profile', 'email', 'User.Read', 'GroupMember.Read.All']);
        }

        return $provider;
    }
}
