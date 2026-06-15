<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Intervention;

/**
 * Secure live view of an intervention for the customer (ex external_link.php).
 * Accessed via an unguessable public token — no login required. Read-only and
 * exposes only customer-safe data (never internal notes, passwords or chat).
 */
class PublicInterventionController extends Controller
{
    public function show(string $token)
    {
        $intervention = Intervention::where('public_token', $token)
            ->with(['client', 'materiel', 'statut', 'prestations'])
            ->firstOrFail();

        // Customer-facing alerts: pending supplier orders / subcontracting returns.
        $commandeEnAttente = $intervention->commandes()->where('recue', false)->min('commande_le');
        $sstEnAttente = $intervention->sousTraitances()->where('retournee', false)->exists();

        return view('public.intervention', compact('intervention', 'commandeEnAttente', 'sstEnAttente'));
    }
}
