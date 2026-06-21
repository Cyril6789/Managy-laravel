<?php

namespace App\Support;

use App\Models\Intervention;

/**
 * Computes the billing breakdown of an intervention:
 *
 *   prestations (catalogue tarifs) − remise client %
 * + pièces (saisies à la volée)    − remise client %
 * − ristourne technicien (€ ou %, sur le sous-total biens, si autorisée)
 * − part des prestations réglée depuis le pack maintenance (heures × tarif net)
 * + déplacement (domicile uniquement)
 * = total à payer en argent
 *
 * Service/part prices come from the catalogue (Paramètres) and the parts entered
 * on the job — never from a free amount typed by the technician. Per-customer
 * percentage discounts are always applied; the manual "ristourne" is optional.
 *
 * The maintenance pack may only settle SERVICE hours (never parts nor travel)
 * and only up to the hours actually logged on the job; doing so is optional —
 * if the customer pays everything in money, the pack is left untouched.
 */
final class Billing
{
    /**
     * @param  float  $maintenanceHeures  service hours settled from the pack
     * @return array{
     *   prestations_brut: float, prestations_remise_pct: float, prestations_net: float,
     *   pieces_brut: float, pieces_remise_pct: float, pieces_net: float,
     *   sous_total: float, ristourne_type: ?string, ristourne_valeur: float, ristourne_montant: float,
     *   maintenance_heures: float, maintenance_montant: float,
     *   deplacement: float, total: float
     * }
     */
    public static function compute(
        Intervention $intervention,
        ?float $deplacement = null,
        ?string $ristourneType = null,
        ?float $ristourneValeur = null,
        float $maintenanceHeures = 0.0,
    ): array {
        $client = $intervention->client;

        $prestaBrut = round($intervention->montantPrestations(), 2);
        $piecesBrut = round($intervention->montantPieces(), 2);

        // Under warranty everything is free: nothing is billed, pack untouched.
        if ($intervention->garantie) {
            return [
                'prestations_brut' => $prestaBrut,
                'prestations_remise_pct' => 0.0,
                'prestations_net' => 0.0,
                'pieces_brut' => $piecesBrut,
                'pieces_remise_pct' => 0.0,
                'pieces_net' => 0.0,
                'sous_total' => 0.0,
                'ristourne_type' => null,
                'ristourne_valeur' => 0.0,
                'ristourne_montant' => 0.0,
                'maintenance_heures' => 0.0,
                'maintenance_montant' => 0.0,
                'deplacement' => 0.0,
                'total' => 0.0,
                'garantie' => true,
            ];
        }

        $prestaPct = (float) ($client?->remise_prestations ?? 0);
        $piecesPct = (float) ($client?->remise_pieces ?? 0);

        $prestaNet = round($prestaBrut * (1 - $prestaPct / 100), 2);
        $piecesNet = round($piecesBrut * (1 - $piecesPct / 100), 2);

        $sousTotal = round($prestaNet + $piecesNet, 2);

        // Manual technician discount on the goods subtotal.
        $ristourneMontant = 0.0;
        $ristourneValeur = (float) ($ristourneValeur ?? 0);
        if ($ristourneValeur > 0) {
            $ristourneMontant = $ristourneType === 'pourcent'
                ? round($sousTotal * $ristourneValeur / 100, 2)
                : min(round($ristourneValeur, 2), $sousTotal);
        } else {
            $ristourneType = null;
        }

        // Maintenance pack: settle part of the SERVICE hours. The euro value is
        // prorated on the net service amount (so customer discounts still apply)
        // and can never exceed the service total.
        $totalHeures = round((float) $intervention->prestations->sum('duree'), 2);
        $maintenanceHeures = max(0.0, min($maintenanceHeures, $totalHeures));
        $maintenanceMontant = $totalHeures > 0
            ? min(round($prestaNet * $maintenanceHeures / $totalHeures, 2), $prestaNet)
            : 0.0;

        // Travel only applies to on-site interventions.
        if ($deplacement === null) {
            $deplacement = $intervention->estDomicile()
                ? Deplacement::montant($client?->ville, (float) ($intervention->deplacement_km ?? 0), (bool) ($client?->deplacement_gratuit))
                : 0.0;
        }
        $deplacement = $intervention->estDomicile() ? round((float) $deplacement, 2) : 0.0;

        $total = round(max(0.0, $sousTotal - $ristourneMontant - $maintenanceMontant + $deplacement), 2);

        return [
            'prestations_brut' => $prestaBrut,
            'prestations_remise_pct' => $prestaPct,
            'prestations_net' => $prestaNet,
            'pieces_brut' => $piecesBrut,
            'pieces_remise_pct' => $piecesPct,
            'pieces_net' => $piecesNet,
            'sous_total' => $sousTotal,
            'ristourne_type' => $ristourneType,
            'ristourne_valeur' => $ristourneValeur,
            'ristourne_montant' => $ristourneMontant,
            'maintenance_heures' => $maintenanceHeures,
            'maintenance_montant' => $maintenanceMontant,
            'deplacement' => $deplacement,
            'total' => $total,
            'garantie' => false,
        ];
    }
}
