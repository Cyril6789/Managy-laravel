<?php

namespace Database\Seeders;

use App\Models\Antivirus;
use App\Models\CommentaireType;
use App\Models\Materiel;
use App\Models\Prestation;
use App\Models\RapportType;
use App\Models\Setting;
use App\Models\Statut;
use App\Models\SystemeExploitation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedAdmin();
        $this->seedReferenceData();

        if (app()->environment('local', 'testing')) {
            $this->call(DemoSeeder::class);
        }
    }

    private function seedSettings(): void
    {
        $defaults = [
            'company_name' => 'Mon Entreprise Informatique',
            'company_email' => 'contact@exemple.fr',
            'company_phone' => '',
            'company_address' => '',
            'company_postal_code' => '',
            'company_city' => '',
            'company_siret' => '',
            'company_vat' => '',
            'company_website' => '',
            'sms_sender' => 'MANAGY',
            'sms_signature' => '',
            'sms_provider' => 'log',          // log | smsmode | smsfactor
            'sms_api_key' => '',
            'company_logo' => null,
            'maintenance_alert_threshold' => '2',
            'statut_attente_id' => null,
            'statut_pret_id' => null,
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedAdmin(): void
    {
        User::firstOrCreate(
            ['pseudo' => 'admin'],
            [
                'prenom' => 'Admin',
                'nom' => 'Managy',
                'email' => 'admin@exemple.fr',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'is_active' => true,
            ],
        );
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
}
