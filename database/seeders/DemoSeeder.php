<?php

namespace Database\Seeders;

use App\Models\Antivirus;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Models\Materiel;
use App\Models\Prestation;
use App\Models\Statut;
use App\Models\SystemeExploitation;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // A non-admin technician with a realistic set of permissions.
        $tech = User::firstOrCreate(
            ['pseudo' => 'tech'],
            [
                'prenom' => 'Julie',
                'nom' => 'Martin',
                'email' => 'julie@exemple.fr',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ],
        );
        $techPerms = [
            Permissions::CLIENTS_VIEW, Permissions::CLIENTS_MANAGE,
            Permissions::INTERVENTIONS_VIEW, Permissions::INTERVENTIONS_CREATE, Permissions::INTERVENTIONS_MANAGE,
            Permissions::CALENDAR_VIEW, Permissions::CALENDAR_MANAGE,
            Permissions::TASKS_VIEW, Permissions::TASKS_MANAGE, Permissions::MESSAGES_SEND,
        ];
        $tech->permissionEntries()->delete();
        foreach ($techPerms as $p) {
            $tech->permissionEntries()->create(['permission' => $p]);
        }

        $admin = User::where('pseudo', 'admin')->first();

        // Customers (a company with a contact, plus an individual).
        $societe = Client::firstOrCreate(['nom' => 'Boulangerie Dupont'], [
            'type' => 'professionnel',
            'civilite' => 'Sté',
            'email' => 'contact@boulangerie-dupont.fr',
            'telephone_fixe' => '0388000000',
            'adresse' => '12 rue des Boulangers',
            'code_postal' => '67000',
            'ville' => 'Strasbourg',
        ]);
        Client::firstOrCreate(['nom' => 'Dupont', 'parent_id' => $societe->id], [
            'type' => 'particulier',
            'civilite' => 'M.',
            'prenom' => 'Jean',
            'email' => 'jean@boulangerie-dupont.fr',
            'telephone_mobile' => '0600000000',
        ]);
        $particulier = Client::firstOrCreate(['nom' => 'Schmitt'], [
            'type' => 'particulier',
            'civilite' => 'Mme',
            'prenom' => 'Claire',
            'email' => 'claire.schmitt@exemple.fr',
            'telephone_mobile' => '0611111111',
            'adresse' => '4 impasse du Lac',
            'code_postal' => '67100',
            'ville' => 'Strasbourg',
        ]);

        if (Intervention::count() > 0) {
            return;
        }

        $statutDefaut = Statut::where('est_defaut', true)->first();
        $statutCours = Statut::where('nom', 'En cours')->first();
        $statutTermine = Statut::where('est_cloture', true)->first();
        $win = SystemeExploitation::where('nom', 'Windows 11')->first();
        $av = Antivirus::first();
        $portable = Materiel::where('nom', 'Ordinateur portable')->first();
        $prestaVirus = Prestation::where('designation', 'Suppression de virus')->first();

        // Open intervention with an appointment.
        $i1 = Intervention::create([
            'client_id' => $societe->id,
            'materiel_id' => $portable?->id,
            'systeme_exploitation_id' => $win?->id,
            'antivirus_id' => $av?->id,
            'statut_id' => $statutCours?->id,
            'opened_by' => $admin?->id,
            'type_lieu' => 'atelier',
            'panne' => 'PC très lent, fenêtres publicitaires intempestives.',
            'materiel_depose' => 'PC portable HP + chargeur',
            'rdv_debut' => now()->addDay()->setTime(9, 0),
            'rdv_fin' => now()->addDay()->setTime(10, 0),
            'urgente' => true,
        ]);
        $i1->techniciens()->attach($tech->id, ['assigned_at' => now()]);
        $i1->prestations()->create([
            'prestation_id' => $prestaVirus?->id,
            'designation' => $prestaVirus?->designation ?? 'Suppression de virus',
            'duree' => 1.5,
        ]);
        InterventionLog::create(['intervention_id' => $i1->id, 'user_id' => $admin?->id, 'texte' => 'a créé l\'intervention', 'created_at' => now()]);

        // Closed intervention.
        $i2 = Intervention::create([
            'client_id' => $particulier->id,
            'materiel_id' => Materiel::where('nom', 'Imprimante')->value('id'),
            'statut_id' => $statutTermine?->id,
            'opened_by' => $admin?->id,
            'type_lieu' => 'domicile',
            'panne' => 'Imprimante ne s\'allume plus.',
            'diagnostic' => 'Alimentation HS, remplacée. Test OK.',
            'message_client' => 'Intervention terminée, imprimante fonctionnelle.',
            'closed_at' => now()->subDays(2),
            'restituted_at' => now()->subDays(2),
            'restituted_by' => $admin?->id,
            'facturee' => true,
        ]);
        $i2->techniciens()->attach($admin?->id, ['assigned_at' => now()->subDays(3)]);
        InterventionLog::create(['intervention_id' => $i2->id, 'user_id' => $admin?->id, 'texte' => 'a clôturé l\'intervention', 'created_at' => now()->subDays(2)]);
    }
}
