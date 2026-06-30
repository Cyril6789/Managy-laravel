<?php

namespace App\Models;

use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Per-société key/value settings (SMS, SMTP, automation, billing, ...).
 *
 * The company identity (name, SIRET, logo, ...) lives on the `societies` table,
 * but is still exposed here under the historical `company_*` keys: reads merge
 * it in and writes are transparently routed to the société row. Every caller
 * (views, services, controllers) keeps working unchanged, per société.
 */
class Setting extends Model
{
    protected $fillable = ['society_id', 'key', 'value'];

    /** company_* setting key => société column. */
    public const COMPANY_FIELDS = [
        'company_name' => 'name',
        'company_email' => 'email',
        'company_phone' => 'phone',
        'company_website' => 'website',
        'company_address' => 'address',
        'company_postal_code' => 'postal_code',
        'company_city' => 'city',
        'company_siret' => 'siret',
        'company_vat' => 'vat',
        'company_logo' => 'logo',
    ];

    protected static function booted(): void
    {
        static::saved(fn (Setting $s) => Cache::forget(static::cacheKey($s->society_id)));
        static::deleted(fn (Setting $s) => Cache::forget(static::cacheKey($s->society_id)));
    }

    private static function cacheKey(?int $societyId): string
    {
        return 'settings.all.'.($societyId ?? 'global');
    }

    private static function currentSocietyId(): ?int
    {
        return app(Tenancy::class)->id();
    }

    /** @return array<string, string|null> */
    public static function all(...$args): array
    {
        $societyId = static::currentSocietyId();

        return Cache::rememberForever(static::cacheKey($societyId), function () use ($societyId) {
            $values = static::query()
                ->when($societyId,
                    fn ($q) => $q->where('society_id', $societyId),
                    fn ($q) => $q->whereNull('society_id'))
                ->pluck('value', 'key')
                ->all();

            // Merge the company identity from the société row.
            if ($societyId && ($society = Society::find($societyId))) {
                foreach (self::COMPANY_FIELDS as $key => $column) {
                    $values[$key] = $society->{$column};
                }
            }

            return $values;
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all()[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $societyId = static::currentSocietyId();

        // Company identity is stored on the société, not in the key/value table.
        if ($societyId && isset(self::COMPANY_FIELDS[$key])) {
            Society::whereKey($societyId)->update([self::COMPANY_FIELDS[$key] => $value]);
            Cache::forget(static::cacheKey($societyId));

            return;
        }

        static::updateOrCreate(
            ['society_id' => $societyId, 'key' => $key],
            ['value' => $value],
        );
    }
}
