<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Computes the travel ("déplacement") fee billed for an on-site (domicile)
 * intervention. The mode is configurable in Paramètres → Facturation:
 *
 *  - aucun   : no travel fee (always 0)
 *  - forfait : a flat fee per visit
 *  - km      : a per-kilometre rate (distance entered by the technician)
 *  - groupes : a fixed rate selected from the customer's city group
 *
 * Regardless of the mode, a configurable list of "free" cities (one per line)
 * always yields a 0 fee when the customer's city matches.
 */
final class Deplacement
{
    public static function mode(): string
    {
        $mode = (string) Setting::get('deplacement_mode', 'aucun');

        return in_array($mode, ['aucun', 'forfait', 'km', 'groupes'], true) ? $mode : 'aucun';
    }

    public static function forfait(): float
    {
        return (float) Setting::get('deplacement_forfait', 0);
    }

    public static function prixKm(): float
    {
        return (float) Setting::get('deplacement_prix_km', 0);
    }

    /** @return list<string> Lower-cased, trimmed free-city names. */
    public static function villesGratuites(): array
    {
        $raw = (string) Setting::get('deplacement_villes_gratuites', '');

        return collect(preg_split('/[\r\n,;]+/', $raw))
            ->map(fn ($v) => mb_strtolower(trim($v)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function villeEstGratuite(?string $ville): bool
    {
        if (! $ville) {
            return false;
        }

        return in_array(mb_strtolower(trim($ville)), self::villesGratuites(), true);
    }

    /** @return array<int, array{name: string, price: float, cities: array<int, string>}> */
    public static function groupesVilles(): array
    {
        $groups = json_decode((string) Setting::get('deplacement_city_groups', '[]'), true);
        if (! is_array($groups)) {
            return [];
        }

        return collect($groups)
            ->filter(fn ($group) => is_array($group))
            ->map(fn (array $group) => [
                'name' => trim((string) ($group['name'] ?? '')),
                'price' => round(max(0, (float) ($group['price'] ?? 0)), 2),
                'cities' => array_values(array_filter(array_map('strval', (array) ($group['cities'] ?? [])))),
            ])
            ->values()
            ->all();
    }

    public static function montantPourVille(?string $ville): float
    {
        $needle = self::normaliserVille($ville);
        if ($needle === '') {
            return 0.0;
        }

        foreach (self::groupesVilles() as $group) {
            foreach ($group['cities'] as $city) {
                if (self::normaliserVille($city) === $needle) {
                    return $group['price'];
                }
            }
        }

        return 0.0;
    }

    /**
     * Compute the travel fee for a customer city (and a distance in km when the
     * mode is per-kilometre). A customer flagged "déplacement gratuit", or a city
     * in the free list, always yields 0.
     */
    public static function montant(?string $ville, ?float $km = null, bool $gratuitClient = false): float
    {
        if ($gratuitClient || self::villeEstGratuite($ville)) {
            return 0.0;
        }

        return match (self::mode()) {
            'forfait' => round(self::forfait(), 2),
            'km' => round(self::prixKm() * (float) ($km ?? 0), 2),
            'groupes' => self::montantPourVille($ville),
            default => 0.0,
        };
    }

    private static function normaliserVille(?string $city): string
    {
        return (string) Str::of((string) $city)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish();
    }
}
