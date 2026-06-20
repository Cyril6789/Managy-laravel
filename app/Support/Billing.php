<?php

namespace App\Support;

use App\Models\Intervention;

/**
 * Computes the billing breakdown of an intervention:
 *
 *   prestations (catalogue tarifs) − remise client %
 * + pièces (saisies à la volée)    − remise client %
 * − ristourne technicien (€ ou %, sur le sous-total biens, si autorisée)
 * + déplacement (domicile uniquement)
 * = total
 *
 * Service/part prices come from the catalogue (Paramètres) and the parts entered
 * on the job — never from a free amount typed by the technician. Per-customer
 * percentage discounts are always applied; the manual "ristourne" is optional.
 */
final class Billing
{
    /**
     * @return array{
     *   prestations_brut: float, prestations_remise_pct: float, prestations_net: float,
     *   pieces_brut: float, pieces_remise_pct: float, pieces_net: float,
     *   sous_total: float, ristourne_type: ?string, ristourne_valeur: float, ristourne_montant: float,
     *   deplacement: float, total: float
     * }
     */
    public static function compute(
        Intervention $intervention,
        ?float $deplacement = null,
        ?string $ristourneType = null,
        ?float $ristourneValeur = null,
    ): array {
        $client = $intervention->client;

        $prestaPct = (float) ($client?->remise_prestations ?? 0);
        $piecesPct = (float) ($client?->remise_pieces ?? 0);

        $prestaBrut = round($intervention->montantPrestations(), 2);
        $piecesBrut = round($intervention->montantPieces(), 2);

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

        // Travel only applies to on-site interventions.
        if ($deplacement === null) {
            $deplacement = $intervention->estDomicile()
                ? Deplacement::montant($client?->ville, (float) ($intervention->deplacement_km ?? 0), (bool) ($client?->deplacement_gratuit))
                : 0.0;
        }
        $deplacement = $intervention->estDomicile() ? round((float) $deplacement, 2) : 0.0;

        $total = round($sousTotal - $ristourneMontant + $deplacement, 2);

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
            'deplacement' => $deplacement,
            'total' => $total,
        ];
    }
}
