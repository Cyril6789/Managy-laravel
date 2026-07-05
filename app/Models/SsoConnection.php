<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToSociety;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A société's Single Sign-On configuration for one provider (Microsoft Entra or
 * Google). The gérant fills in the OAuth credentials; once enabled, team members
 * can sign in with that provider from the login page.
 */
class SsoConnection extends Model
{
    use Auditable, BelongsToSociety, SoftDeletes;

    public const PROVIDER_MICROSOFT = 'microsoft';

    public const PROVIDER_GOOGLE = 'google';

    public const PROVIDERS = [
        self::PROVIDER_MICROSOFT => 'Microsoft Entra ID',
        self::PROVIDER_GOOGLE => 'Google Workspace',
    ];

    protected $fillable = [
        'society_id',
        'provider',
        'enabled',
        'client_id',
        'client_secret',
        'tenant_id',
        'allowed_domains',
        'auto_provision_users',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'auto_provision_users' => 'boolean',
            // Secret never sits in the database in clear text.
            'client_secret' => 'encrypted',
        ];
    }

    /** Whether the connection can actually be used to sign in. */
    public function isUsable(): bool
    {
        return $this->enabled && filled($this->client_id) && filled($this->client_secret);
    }

    public function providerLabel(): string
    {
        return self::PROVIDERS[$this->provider] ?? ucfirst($this->provider);
    }

    /** @return list<string> lower-cased e-mail domains this connection accepts. */
    public function domains(): array
    {
        return collect(explode(',', (string) $this->allowed_domains))
            ->map(fn ($d) => Str::lower(trim(ltrim($d, '@'))))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** Does the given e-mail belong to one of the accepted domains? */
    public function acceptsEmail(?string $email): bool
    {
        $domains = $this->domains();

        if ($domains === []) {
            // No restriction configured: accept any address.
            return true;
        }

        $emailDomain = Str::lower(Str::after((string) $email, '@'));

        return $emailDomain !== '' && in_array($emailDomain, $domains, true);
    }
}
