<?php

namespace App\Services;

use App\Models\Antivirus;
use App\Models\CommentaireType;
use App\Models\Materiel;
use App\Models\Prestation;
use App\Models\RapportType;
use App\Models\Setting;
use App\Models\Society;
use App\Models\Statut;
use App\Models\SystemeExploitation;
use App\Support\Tenancy;

/**
 * Fills a freshly created société with sensible starter data: intervention
 * statuses, device types, operating systems, antivirus products, a service
 * catalogue and a couple of templates — so the new space is usable right away.
 *
 * Everything runs inside the société's tenant context, so the BelongsToSociety
 * trait stamps every row with the right society_id automatically.
 */
class SocietyProvisioner
{
    public function provision(Society $society): void
    {
        app(Tenancy::class)->forSociety($society->id, function () {
            $this->seedReferenceData();
            $this->seedSettings();
        });
    }

    private function seedReferenceData(): void
    {
        $statuts = [
            ['nom' => 'Nouvelle', 'couleur' => '#3b82f6', 'est_defaut' => true],
            ['nom' => 'En attente', 'couleur' => '#f59e0b'],
            ['nom' => 'En cours', 'couleur' => '#6366f1'],
            ['nom' => 'En attente client', 'couleur' => '#f97316'],
            ['nom' => 'Devis envoyé', 'couleur' => '#a855f7'],
            ['nom' => 'Prête', 'couleur' => '#10b981'],
            ['nom' => 'Terminée', 'couleur' => '#16a34a', 'est_cloture' => true],
            ['nom' => 'Annulée', 'couleur' => '#ef4444', 'est_cloture' => true],
        ];
        foreach ($statuts as $i => $s) {
            Statut::firstOrCreate(['nom' => $s['nom']], $s + ['ordre' => $i]);
        }

        foreach (['Ordinateur fixe', 'Ordinateur portable', 'Serveur', 'Imprimante', 'NAS', 'Téléphone', 'Tablette', 'Périphérique', 'Réseau'] as $nom) {
            Materiel::firstOrCreate(['nom' => $nom]);
        }

        foreach (['Windows 11', 'Windows 10', 'Windows Server', 'macOS', 'Linux', 'Android', 'iOS', 'Aucun / N.C.'] as $nom) {
            SystemeExploitation::firstOrCreate(['nom' => $nom]);
        }

        foreach (['Windows Defender', 'Bitdefender', 'ESET', 'Kaspersky', 'Avast', 'Malwarebytes', 'Aucun'] as $nom) {
            Antivirus::firstOrCreate(['nom' => $nom]);
        }

        $prestations = [
            ['designation' => 'Diagnostic matériel', 'duree_defaut' => 0.5],
            ['designation' => 'Nettoyage / dépoussiérage', 'duree_defaut' => 0.5],
            ['designation' => 'Suppression de virus', 'duree_defaut' => 1.5],
            ['designation' => 'Réinstallation système', 'duree_defaut' => 2],
            ['designation' => 'Récupération de données', 'duree_defaut' => 2],
            ['designation' => 'Remplacement de composant', 'duree_defaut' => 1],
            ['designation' => 'Installation logiciel', 'duree_defaut' => 0.5],
            ['designation' => 'Intervention sur site', 'duree_defaut' => 1],
        ];
        foreach ($prestations as $p) {
            Prestation::firstOrCreate(['designation' => $p['designation']], $p);
        }

        RapportType::firstOrCreate(['titre' => 'Diagnostic standard'], [
            'texte' => "Diagnostic effectué.\nÉtat constaté : \nActions réalisées : \nRecommandations : ",
        ]);
        CommentaireType::firstOrCreate(['titre' => 'Matériel prêt'], [
            'texte' => 'Votre matériel est prêt et disponible. Vous pouvez venir le récupérer aux horaires d\'ouverture.',
        ]);
    }

    private function seedSettings(): void
    {
        $defaults = [
            'sms_sender' => 'MANAGY',
            'sms_signature' => '',
            'sms_provider' => 'log',
            'sms_api_key' => '',
            'mail_host' => '',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => '',
            'mail_from_name' => '',
            'maintenance_alert_threshold' => '2',
        ];

        foreach ($defaults as $key => $value) {
            Setting::put($key, $value);
        }

        // Wire up the order / subcontracting status automation out of the box.
        Setting::put('statut_attente_id', Statut::where('nom', 'En attente')->value('id'));
        Setting::put('statut_pret_id', Statut::where('nom', 'En cours')->value('id'));
    }
}
