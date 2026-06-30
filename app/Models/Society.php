<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * A SaaS tenant. Holds the company identity (name, SIRET, logo, ...) and owns
 * every business record through the society_id foreign key.
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

        // Company identity is exposed through Setting::all() (cached per société).
        static::saved(fn (Society $s) => Cache::forget('settings.all.'.$s->id));
        static::deleted(fn (Society $s) => Cache::forget('settings.all.'.$s->id));
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'societe';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
