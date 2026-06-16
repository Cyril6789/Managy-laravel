<?php

namespace App\Support;

use App\Models\Intervention;
use App\Models\Setting;
use App\Models\Statut;

/**
 * Keeps an intervention's status in sync with its pending dependencies
 * (supplier orders / subcontracting). The two statuses are configurable in
 * Paramètres → Statuts → Automatisation; sensible fallbacks are used otherwise.
 */
final class InterventionStatus
{
    public static function syncFromDependencies(Intervention $intervention): void
    {
        if ($intervention->estCloturee()) {
            return;
        }

        $waitingId = self::waitingStatusId();
        $readyId = self::readyStatusId();

        if (! $waitingId) {
            return; // no suitable "waiting" status available
        }

        $pending = $intervention->commandes()->where('recue', false)->exists()
            || $intervention->sousTraitances()->where('retournee', false)->exists();

        if ($pending && (int) $intervention->statut_id !== $waitingId) {
            $intervention->update(['statut_id' => $waitingId]);
        } elseif (! $pending && $readyId && (int) $intervention->statut_id === $waitingId) {
            // Only revert when we were the ones who set the waiting status.
            $intervention->update(['statut_id' => $readyId]);
        }
    }

    /** Configured "waiting" status, else the first non-closing status named "…attente…". */
    private static function waitingStatusId(): int
    {
        return (int) Setting::get('statut_attente_id')
            ?: (int) Statut::where('est_cloture', false)
                ->where('nom', 'like', '%attente%')
                ->orderBy('ordre')
                ->value('id');
    }

    /** Configured "ready" status, else "…cours…", else the default opening status. */
    private static function readyStatusId(): int
    {
        return (int) Setting::get('statut_pret_id')
            ?: (int) (Statut::where('est_cloture', false)->where('nom', 'like', '%cours%')->orderBy('ordre')->value('id')
                ?: Statut::where('est_defaut', true)->value('id'));
    }
}
