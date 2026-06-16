<?php

namespace App\Support;

use App\Models\Intervention;
use App\Models\Setting;

/**
 * Keeps an intervention's status in sync with its pending dependencies
 * (supplier orders / subcontracting). The two statuses are configurable in
 * Paramètres → Statuts: "en attente de réception" and "réception faite".
 */
final class InterventionStatus
{
    public static function syncFromDependencies(Intervention $intervention): void
    {
        if ($intervention->estCloturee()) {
            return;
        }

        $waitingId = (int) Setting::get('statut_attente_id');
        $readyId = (int) Setting::get('statut_pret_id');

        if (! $waitingId) {
            return; // automation disabled until configured
        }

        $pending = $intervention->commandes()->where('recue', false)->exists()
            || $intervention->sousTraitances()->where('retournee', false)->exists();

        if ($pending && $intervention->statut_id !== $waitingId) {
            $intervention->update(['statut_id' => $waitingId]);
        } elseif (! $pending && $readyId && $intervention->statut_id === $waitingId) {
            // Only revert when we were the ones who set the waiting status.
            $intervention->update(['statut_id' => $readyId]);
        }
    }
}
