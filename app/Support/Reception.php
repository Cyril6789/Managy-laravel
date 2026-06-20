<?php

namespace App\Support;

use App\Models\Commande;
use App\Models\InterventionLog;
use App\Models\SousTraitance;
use App\Services\AutomatismeRunner;
use App\Services\Notifier;
use Illuminate\Support\Facades\Auth;

/**
 * Receiving a supplier order or a subcontracting return — the single place that
 * marks the item done, unblocks the intervention (status sync), records the log,
 * runs the matching automatisme and notifies the technician(s) in charge.
 *
 * Both the intervention panel and the standalone "en cours" pages go through
 * here, so that whoever physically receives the parcel / return (often a
 * receptionist, not the assigned technician) triggers the exact same effects.
 */
final class Reception
{
    /** Mark a supplier order as received. Idempotent. */
    public static function receiveCommande(Commande $commande): void
    {
        if ($commande->recue) {
            return;
        }

        $commande->forceFill(['recue' => true, 'recue_le' => $commande->recue_le ?? now()])->save();

        $intervention = $commande->intervention;
        $libelle = $commande->numero_commande ?: $commande->fournisseur ?: '';

        self::log($intervention->id, 'a réceptionné la commande '.$libelle);
        app(AutomatismeRunner::class)->fire('commande_recue', $intervention);
        InterventionStatus::syncFromDependencies($intervention->refresh());
        Notifier::interventionChanged($intervention, 'Commande reçue'.($libelle ? ' ('.$libelle.')' : ''));
    }

    /** Mark a subcontracting as returned. Idempotent. */
    public static function returnSousTraitance(SousTraitance $sst): void
    {
        if ($sst->retournee) {
            return;
        }

        $sst->forceFill(['retournee' => true, 'retour_le' => $sst->retour_le ?? now()])->save();

        $intervention = $sst->intervention;
        $libelle = $sst->nom ?: $sst->numero_commande ?: '';

        self::log($intervention->id, 'a réceptionné le retour de sous-traitance '.$libelle);
        app(AutomatismeRunner::class)->fire('sous_traitance_retour', $intervention);
        InterventionStatus::syncFromDependencies($intervention->refresh());
        Notifier::interventionChanged($intervention, 'Retour de sous-traitance reçu'.($libelle ? ' ('.$libelle.')' : ''));
    }

    private static function log(int $interventionId, string $texte): void
    {
        InterventionLog::create([
            'intervention_id' => $interventionId,
            'user_id' => Auth::id(),
            'texte' => trim($texte),
            'created_at' => now(),
        ]);
    }
}
