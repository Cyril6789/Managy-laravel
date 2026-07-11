<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * A SaaS tenant. Holds the company identity and owns every business record
 * through the society_id foreign key.
 */
class Society extends Model
{
    protected $table = 'societies';

    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'address', 'postal_code', 'city',
        'siret', 'vat', 'website', 'logo', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Society $society) {
            if (empty($society->slug)) {
                $society->slug = static::uniqueSlug($society->name);
            }
        });

        static::saved(fn (Society $s) => Cache::forget('settings.all.'.$s->id));
        static::deleted(fn (Society $s) => Cache::forget('settings.all.'.$s->id));
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function normalizeSlug(string $value): string
    {
        return Str::slug($value);
    }

    public static function slugIsAvailable(string $slug, ?int $ignoreSocietyId = null): bool
    {
        $slug = static::normalizeSlug($slug);

        if ($slug === '' || in_array($slug, config('saas.reserved_subdomains', []), true)) {
            return false;
        }

        return ! static::query()
            ->when($ignoreSocietyId, fn ($query) => $query->whereKeyNot($ignoreSocietyId))
            ->where('slug', $slug)
            ->exists();
    }

    public static function uniqueSlug(string $value, ?int $ignoreSocietyId = null): string
    {
        $base = static::normalizeSlug($value) ?: 'societe';
        $slug = $base;
        $i = 2;

        while (! static::slugIsAvailable($slug, $ignoreSocietyId)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
