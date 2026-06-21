<?php

namespace Database\Seeders;

use App\Models\Antivirus;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\Materiel;
use App\Models\Prestation;
use App\Models\Setting;
use App\Models\Statut;
use App\Models\SystemeExploitation;
use App\Models\TechnicianAbsence;
use App\Models\User;
use App\Support\Permissions;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds a large, lived-in dataset — as if the workshop had been running for
 * ~10 years: extra technicians, thousands of customers (pro & private with
 * their contacts), thousands of interventions (80% closed, workshop & on-site),
 * tasks, technician leave, satisfaction surveys, supplier orders,
 * subcontracting, internal/public chat, billing, maintenance packs…
 *
 * Run it on its own:  php artisan db:seed --class=Database\\Seeders\\RealisticDataSeeder
 * Scale it down/up:    SEED_SCALE=0.1 php artisan db:seed --class=...   (default 1.0)
 * Force a re-run:      REALISTIC_SEED_FORCE=1 php artisan db:seed --class=...
 */
class RealisticDataSeeder extends Seeder
{
    private Generator $faker;

    /** Base volumes (multiplied by SEED_SCALE). */
    private const COMPANIES = 400;

    private const PARTICULIERS = 1600;

    private const INTERVENTIONS = 4000;

    private const TASKS = 500;

    private const EVENTS = 400;

    /** Bulk-insert accumulators, flushed every FLUSH_EVERY interventions. */
    private const FLUSH_EVERY = 500;

    /** @var array<string, array<int, array<string, mixed>>> */
    private array $buffers = [];

    public function run(): void
    {
        if (Intervention::count() > 2000 && ! env('REALISTIC_SEED_FORCE')) {
            $this->command?->warn(
                'RealisticDataSeeder: '.Intervention::count().' interventions déjà présentes. '
                .'Lancez `migrate:fresh` ou définissez REALISTIC_SEED_FORCE=1 pour forcer.'
            );

            return;
        }

        $this->faker = Factory::create('fr_FR');
        $scale = max(0.001, (float) env('SEED_SCALE', 1.0));

        $this->command?->info('Génération de données réalistes (cela peut prendre une minute)…');

        DB::transaction(function () use ($scale) {
            $ref = $this->ensureReferenceData();
            $techs = $this->seedTechnicians();
            [$clientIds, $clientMeta] = $this->seedClients($scale);
            $this->seedInterventions($scale, $techs, $ref, $clientIds, $clientMeta);
            $this->seedMaintenancePacks($techs, $clientMeta);
            $this->seedTasksAndEvents($scale, $techs, $clientIds);
            $this->seedAbsencesAndNotes($techs);
        });

        Setting::put('realistic_seeded_at', now()->toDateTimeString());

        $this->command?->info(sprintf(
            'Terminé ✔  %d clients · %d interventions · %d techniciens',
            Client::count(),
            Intervention::count(),
            User::where('is_admin', false)->count(),
        ));
    }

    // ------------------------------------------------------------------ Reference

    /**
     * Make the seeder self-sufficient: guarantee the reference lists and the
     * admin account exist even when run without DatabaseSeeder first.
     *
     * @return array<string, mixed>
     */
    private function ensureReferenceData(): array
    {
        User::firstOrCreate(['pseudo' => 'admin'], [
            'prenom' => 'Admin', 'nom' => 'Managy', 'email' => 'admin@exemple.fr',
            'password' => Hash::make('password'), 'is_admin' => true, 'is_active' => true,
        ]);

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
            ['designation' => 'Diagnostic matériel', 'duree_defaut' => 0.5, 'tarif' => 55],
            ['designation' => 'Nettoyage / dépoussiérage', 'duree_defaut' => 0.5, 'tarif' => 55],
            ['designation' => 'Suppression de virus', 'duree_defaut' => 1.5, 'tarif' => 60],
            ['designation' => 'Réinstallation système', 'duree_defaut' => 2, 'tarif' => 60],
            ['designation' => 'Récupération de données', 'duree_defaut' => 2, 'tarif' => 70],
            ['designation' => 'Remplacement de composant', 'duree_defaut' => 1, 'tarif' => 60],
            ['designation' => 'Installation logiciel', 'duree_defaut' => 0.5, 'tarif' => 55],
            ['designation' => 'Intervention sur site', 'duree_defaut' => 1, 'tarif' => 65],
            ['designation' => 'Configuration réseau', 'duree_defaut' => 1.5, 'tarif' => 65],
            ['designation' => 'Migration de poste', 'duree_defaut' => 2.5, 'tarif' => 60],
        ];
        foreach ($prestations as $p) {
            Prestation::firstOrCreate(['designation' => $p['designation']], $p);
        }
        // Make sure catalogue lines are priced (older seed left tarif null).
        Prestation::whereNull('tarif')->update(['tarif' => 60]);

        return [
            'statuts' => Statut::pluck('id', 'nom'),
            'materiels' => Materiel::pluck('id')->all(),
            'os' => SystemeExploitation::pluck('id')->all(),
            'antivirus' => Antivirus::pluck('id')->all(),
            'prestations' => Prestation::all()->map(fn ($p) => [
                'id' => $p->id, 'designation' => $p->designation,
                'duree' => (float) $p->duree_defaut, 'tarif' => (float) ($p->tarif ?: 60),
            ])->all(),
        ];
    }

    // ------------------------------------------------------------------ Technicians

    /**
     * @return array{pool: array<int, User>, ex: User, admin: User, openers: array<int, User>}
     */
    private function seedTechnicians(): array
    {
        $base = [
            Permissions::CLIENTS_VIEW, Permissions::CLIENTS_MANAGE,
            Permissions::INTERVENTIONS_VIEW, Permissions::INTERVENTIONS_CREATE, Permissions::INTERVENTIONS_MANAGE,
            Permissions::COMMANDES_RECEPTION, Permissions::SOUS_TRAITANCES_RECEPTION,
            Permissions::CALENDAR_VIEW, Permissions::CALENDAR_MANAGE,
            Permissions::TASKS_VIEW, Permissions::TASKS_MANAGE,
            Permissions::MESSAGES_SEND, Permissions::MAINTENANCE_VIEW, Permissions::SATISFACTION_VIEW,
            Permissions::STATS_VIEW,
        ];
        $senior = array_merge($base, [
            Permissions::INTERVENTIONS_VIEW_ALL, Permissions::INTERVENTIONS_DECLOTURE,
            Permissions::INTERVENTIONS_FACTURATION, Permissions::INTERVENTIONS_ASSIGN,
            Permissions::INTERVENTIONS_RISTOURNE, Permissions::CLIENTS_REMISES,
            Permissions::MAINTENANCE_MANAGE, Permissions::LOGS_VIEW,
        ]);

        $defs = [
            ['pseudo' => 'tech', 'prenom' => 'Julie', 'nom' => 'Martin', 'senior' => false, 'active' => true],
            ['pseudo' => 'lucas', 'prenom' => 'Lucas', 'nom' => 'Bernard', 'senior' => true, 'active' => true],
            ['pseudo' => 'emma', 'prenom' => 'Emma', 'nom' => 'Petit', 'senior' => false, 'active' => true],
            ['pseudo' => 'hugo', 'prenom' => 'Hugo', 'nom' => 'Moreau', 'senior' => false, 'active' => true],
            ['pseudo' => 'lea', 'prenom' => 'Léa', 'nom' => 'Garnier', 'senior' => false, 'active' => true],
            ['pseudo' => 'nathan', 'prenom' => 'Nathan', 'nom' => 'Roux', 'senior' => true, 'active' => true],
            ['pseudo' => 'thomas', 'prenom' => 'Thomas', 'nom' => 'Girard', 'senior' => false, 'active' => false],
        ];

        $pool = [];
        $ex = null;
        foreach ($defs as $d) {
            $user = User::firstOrCreate(['pseudo' => $d['pseudo']], [
                'prenom' => $d['prenom'],
                'nom' => $d['nom'],
                'email' => Str::slug($d['prenom']).'@exemple.fr',
                'telephone' => $this->faker->mobileNumber(),
                'password' => Hash::make('password'),
                'is_admin' => false,
                'is_active' => $d['active'],
                'last_action_at' => $d['active'] ? now()->subMinutes(random_int(1, 600)) : null,
            ]);

            $perms = $d['senior'] ? $senior : $base;
            $user->permissionEntries()->delete();
            $user->permissionEntries()->insert(array_map(
                fn ($p) => ['user_id' => $user->id, 'permission' => $p],
                $perms,
            ));

            if ($d['active']) {
                $pool[] = $user;
            } else {
                $ex = $user;
            }
        }

        $admin = User::where('pseudo', 'admin')->first();

        return [
            'pool' => $pool,                       // active technicians who take jobs
            'ex' => $ex,                           // former employee (closed history only)
            'admin' => $admin,
            'openers' => array_merge($pool, [$admin]),
        ];
    }

    // ------------------------------------------------------------------ Clients

    /**
     * @return array{0: array{pro: array<int,int>, particulier: array<int,int>}, 1: array<int, array<string,mixed>>}
     */
    private function seedClients(float $scale): array
    {
        $companyCount = (int) round(self::COMPANIES * $scale);
        $particulierCount = (int) round(self::PARTICULIERS * $scale);

        $pro = [];
        $particulier = [];
        $meta = [];   // id => ['created' => Carbon, 'gratuit' => bool, 'recipient' => clientId, 'contacts' => int[], 'pro' => bool]

        for ($i = 0; $i < $companyCount; $i++) {
            $created = Carbon::instance($this->faker->dateTimeBetween('-10 years', 'now'));
            $gratuit = $this->faker->boolean(8);
            $c = new Client([
                'type' => 'professionnel',
                'civilite' => 'Sté',
                'nom' => $this->faker->company(),
                'email' => $this->faker->companyEmail(),
                'telephone_fixe' => $this->faker->phoneNumber(),
                'telephone_mobile' => $this->faker->boolean(40) ? $this->faker->mobileNumber() : null,
                'adresse' => $this->faker->streetAddress(),
                'code_postal' => $this->faker->postcode(),
                'ville' => $this->faker->city(),
                'siret' => $this->faker->siret(),
                'deplacement_gratuit' => $gratuit,
                'remise_prestations' => $this->faker->boolean(15) ? $this->faker->randomElement([5, 10, 15, 20]) : null,
                'remise_pieces' => $this->faker->boolean(10) ? $this->faker->randomElement([5, 10]) : null,
                'notes' => $this->faker->boolean(25) ? $this->faker->sentence() : null,
            ]);
            $c->created_at = $created;
            $c->updated_at = $created;
            $c->save();

            $pro[] = $c->id;
            $meta[$c->id] = ['created' => $created, 'gratuit' => $gratuit, 'recipient' => $c->id, 'contacts' => [], 'pro' => true];
        }

        for ($i = 0; $i < $particulierCount; $i++) {
            $created = Carbon::instance($this->faker->dateTimeBetween('-10 years', 'now'));
            $civ = $this->faker->randomElement(['M.', 'Mme']);
            $gratuit = $this->faker->boolean(3);
            $hasAddress = $this->faker->boolean(75);
            $c = new Client([
                'type' => 'particulier',
                'civilite' => $civ,
                'nom' => $this->faker->lastName(),
                'prenom' => $civ === 'Mme' ? $this->faker->firstNameFemale() : $this->faker->firstNameMale(),
                'email' => $this->faker->boolean(85) ? $this->faker->safeEmail() : null,
                'telephone_mobile' => $this->faker->mobileNumber(),
                'telephone_fixe' => $this->faker->boolean(35) ? $this->faker->phoneNumber() : null,
                'adresse' => $hasAddress ? $this->faker->streetAddress() : null,
                'code_postal' => $hasAddress ? $this->faker->postcode() : null,
                'ville' => $hasAddress ? $this->faker->city() : null,
                'deplacement_gratuit' => $gratuit,
            ]);
            $c->created_at = $created;
            $c->updated_at = $created;
            $c->save();

            $particulier[] = $c->id;
            $meta[$c->id] = ['created' => $created, 'gratuit' => $gratuit, 'recipient' => $c->id, 'contacts' => [], 'pro' => false];
        }

        // Wire up company ↔ contact (a particulier acting as a company's contact).
        $pivot = [];
        foreach ($pro as $companyId) {
            if (empty($particulier) || ! $this->faker->boolean(65)) {
                continue;
            }
            $n = random_int(1, min(3, count($particulier)));
            $picked = (array) array_rand(array_flip($particulier), $n);
            foreach ($picked as $contactId) {
                $contactId = (int) $contactId;
                $meta[$companyId]['contacts'][] = $contactId;
                $pivot[] = [
                    'company_id' => $companyId,
                    'contact_id' => $contactId,
                    'created_at' => $meta[$companyId]['created'],
                    'updated_at' => $meta[$companyId]['created'],
                ];
            }
        }
        foreach (array_chunk($pivot, 500) as $chunk) {
            DB::table('company_contact')->insert($chunk);
        }

        $this->command?->info('  · '.count($pro).' pros · '.count($particulier).' particuliers');

        return [['pro' => $pro, 'particulier' => $particulier], $meta];
    }

    // ------------------------------------------------------------------ Interventions

    /**
     * @param  array{pool: array<int,User>, ex: User, admin: User, openers: array<int,User>}  $techs
     * @param  array<string,mixed>  $ref
     * @param  array{pro: array<int,int>, particulier: array<int,int>}  $clientIds
     * @param  array<int, array<string,mixed>>  $meta
     */
    private function seedInterventions(float $scale, array $techs, array $ref, array $clientIds, array $meta): void
    {
        $total = (int) round(self::INTERVENTIONS * $scale);
        $exDeparture = now()->subYears(2);

        $statuts = $ref['statuts'];
        $openStatusIds = collect(['Nouvelle', 'En attente', 'En cours', 'En attente client', 'Devis envoyé', 'Prête'])
            ->map(fn ($n) => $statuts[$n])->all();
        $termineId = $statuts['Terminée'];
        $annuleId = $statuts['Annulée'];

        for ($n = 0; $n < $total; $n++) {
            $useCompany = $this->faker->boolean(40) && ! empty($clientIds['pro']);
            $clientId = $useCompany
                ? $clientIds['pro'][array_rand($clientIds['pro'])]
                : $clientIds['particulier'][array_rand($clientIds['particulier'])];
            $m = $meta[$clientId];

            $contactId = null;
            if ($useCompany && $m['contacts'] && $this->faker->boolean(60)) {
                $contactId = $m['contacts'][array_rand($m['contacts'])];
            }
            $recipientId = $contactId ?: $clientId;
            $freeTravel = $m['gratuit'];

            $openedAt = Carbon::instance($this->faker->dateTimeBetween($m['created'], 'now'))
                ->setTime(random_int(8, 18), $this->faker->randomElement([0, 15, 30, 45]));
            $daysAgo = $openedAt->diffInDays(now());

            $domicile = $this->faker->boolean(35);
            $recent = $daysAgo < 5;
            $closed = $recent ? $this->faker->boolean(35) : $this->faker->boolean(82);

            $assigned = $this->pickTechnicians($techs, $openedAt, $exDeparture);
            $opener = $this->faker->boolean(35)
                ? $techs['openers'][array_rand($techs['openers'])]
                : $assigned[0];

            $attrs = [
                'client_id' => $clientId,
                'contact_id' => $contactId,
                'materiel_id' => $ref['materiels'][array_rand($ref['materiels'])],
                'systeme_exploitation_id' => $this->faker->boolean(85) ? $ref['os'][array_rand($ref['os'])] : null,
                'antivirus_id' => $this->faker->boolean(70) ? $ref['antivirus'][array_rand($ref['antivirus'])] : null,
                'opened_by' => $opener->id,
                'type_lieu' => $domicile ? 'domicile' : 'atelier',
                'priorite' => $this->faker->randomElement([0, 0, 0, 1, 2]),
                'urgente' => $this->faker->boolean(12),
                'garantie' => $this->faker->boolean(8),
                'panne' => $this->faker->randomElement(self::PANNES),
                'materiel_depose' => $this->faker->boolean(70) ? $this->faker->randomElement(self::MATERIELS_DEPOSES) : null,
                'mdp' => $this->faker->boolean(30) ? $this->faker->bothify('????##') : null,
                'message_interne' => $this->faker->boolean(20) ? $this->faker->sentence() : null,
                'opened_at' => $openedAt,
            ];

            // Build the priced service & parts lines first (we need the totals).
            [$prestationLines, $montantPrestations, $heures] = $this->buildPrestations($ref, $closed);
            [$pieceLines, $montantPieces] = $this->buildPieces();

            $lastActivity = $openedAt->copy();

            if ($closed) {
                $cancelled = $this->faker->boolean(10);
                if ($cancelled) {
                    $closedAt = $openedAt->copy()->addDays(random_int(1, 20));
                    $attrs += [
                        'statut_id' => $annuleId,
                        'closed_at' => $closedAt,
                        'message_client' => 'Intervention annulée à la demande du client.',
                        'diagnostic' => $this->faker->boolean(50) ? 'Devis refusé / appareil non réparable économiquement.' : null,
                    ];
                    $lastActivity = $closedAt;
                    // A cancelled job carries no billing.
                    $prestationLines = array_slice($prestationLines, 0, 1);
                    $montantPrestations = $this->sumPrestations($prestationLines);
                    $pieceLines = [];
                    $montantPieces = 0.0;
                } else {
                    $finalisee = $openedAt->copy()->addHours(random_int(2, 24 * 8));
                    $restitue = $finalisee->copy()->addHours(random_int(0, 24 * 4));
                    $billing = $this->computeBilling($domicile, $freeTravel, $montantPrestations, $montantPieces);
                    $signed = $this->faker->boolean(55);

                    $attrs += [
                        'statut_id' => $termineId,
                        'diagnostic' => $this->faker->randomElement(self::DIAGNOSTICS),
                        'message_client' => $this->faker->randomElement(self::MESSAGES_CLIENT),
                        'restituted_by' => $assigned[0]->id,
                        'finalisee_at' => $finalisee,
                        'restituted_at' => $restitue,
                        'closed_at' => $restitue,
                        'facturee' => $this->faker->boolean(90),
                    ] + $billing;

                    if ($signed) {
                        $attrs['signataire_nom'] = $this->faker->name();
                        $attrs['signed_at'] = $restitue;
                        $attrs['signature_path'] = 'signatures/'.Str::uuid().'.png';
                    }
                    $lastActivity = $restitue;
                }
            } else {
                $attrs['statut_id'] = $openStatusIds[array_rand($openStatusIds)];
                if ($this->faker->boolean(40)) {
                    $attrs['tarif_estimatif'] = $this->faker->randomElement([49, 79, 99, 120, 150, 200]);
                }
                // On-site / scheduled jobs carry an appointment.
                if ($domicile || $this->faker->boolean(35)) {
                    $start = $recent
                        ? now()->copy()->addDays(random_int(0, 21))->setTime(random_int(8, 17), 0)
                        : $openedAt->copy()->addDays(random_int(0, 7))->setTime(random_int(8, 17), 0);
                    $attrs['rdv_debut'] = $start;
                    $attrs['rdv_fin'] = $start->copy()->addHours(random_int(1, 3));
                    $attrs['rdv_annule'] = $this->faker->boolean(8);
                }
            }

            // Persist the intervention (Eloquent → public_token + reference hooks).
            $i = new Intervention($attrs);
            $i->created_at = $openedAt;
            $i->updated_at = $lastActivity;
            $i->save();
            $id = $i->id;

            // ----- Children (buffered) -----
            foreach ($assigned as $k => $tech) {
                $this->buffers['intervention_user'][] = [
                    'intervention_id' => $id, 'user_id' => $tech->id,
                    'assigned_at' => $openedAt->copy()->addHours($k),
                ];
            }
            foreach ($prestationLines as $line) {
                $this->buffers['intervention_prestations'][] = $line + [
                    'intervention_id' => $id, 'created_at' => $openedAt, 'updated_at' => $lastActivity,
                ];
            }
            foreach ($pieceLines as $line) {
                $this->buffers['intervention_pieces'][] = $line + [
                    'intervention_id' => $id, 'created_at' => $openedAt, 'updated_at' => $lastActivity,
                ];
            }

            $this->buildLogs($id, $opener, $assigned, $openedAt, $closed, $lastActivity);
            $this->buildChats($id, $assigned, $openedAt, $lastActivity);
            $this->buildClientMessages($id, $recipientId, $assigned[0], $openedAt, $lastActivity, $closed, $meta[$recipientId]);
            $this->buildOrders($id, $openedAt, $closed, $lastActivity);
            $this->buildPhotos($id, $assigned[0], $openedAt, $lastActivity);

            if ($closed && isset($attrs['restituted_at'])) {
                $this->buildSatisfaction($id, $recipientId, $lastActivity);
            }

            if ($n > 0 && $n % self::FLUSH_EVERY === 0) {
                $this->flush();
                $this->command?->info('  · '.$n.' interventions…');
            }
        }

        $this->flush();
        $this->command?->info('  · '.$total.' interventions générées');
    }

    /**
     * @param  array{pool: array<int,User>, ex: User}  $techs
     * @return array<int, User>
     */
    private function pickTechnicians(array $techs, Carbon $openedAt, Carbon $exDeparture): array
    {
        $pool = $techs['pool'];
        // A former employee shows up on the older (closed) history.
        if ($techs['ex'] && $openedAt->lt($exDeparture) && $this->faker->boolean(20)) {
            $pool = array_merge($pool, [$techs['ex']]);
        }
        shuffle($pool);
        $count = $this->faker->boolean(28) ? 2 : 1;

        return array_slice($pool, 0, min($count, count($pool)));
    }

    /**
     * @param  array<string,mixed>  $ref
     * @return array{0: array<int,array<string,mixed>>, 1: float, 2: float}
     */
    private function buildPrestations(array $ref, bool $closed): array
    {
        $catalogue = $ref['prestations'];
        $count = $closed ? random_int(1, 4) : random_int(0, 2);
        $keys = (array) array_rand($catalogue, min($count ?: 1, count($catalogue)));
        if ($count === 0) {
            return [[], 0.0, 0.0];
        }

        $lines = [];
        $total = 0.0;
        $heures = 0.0;
        foreach (array_slice($keys, 0, $count) as $key) {
            $p = $catalogue[$key];
            $duree = max(0.25, round($p['duree'] * $this->faker->randomFloat(2, 0.75, 1.75), 2));
            $tarif = $p['tarif'];
            $lines[] = [
                'prestation_id' => $p['id'],
                'designation' => $p['designation'],
                'duree' => $duree,
                'tarif' => $tarif,
            ];
            $total += $duree * $tarif;
            $heures += $duree;
        }

        return [$lines, round($total, 2), round($heures, 2)];
    }

    /** @param array<int,array<string,mixed>> $lines */
    private function sumPrestations(array $lines): float
    {
        return round(array_sum(array_map(fn ($l) => $l['duree'] * $l['tarif'], $lines)), 2);
    }

    /** @return array{0: array<int,array<string,mixed>>, 1: float} */
    private function buildPieces(): array
    {
        if (! $this->faker->boolean(35)) {
            return [[], 0.0];
        }
        $lines = [];
        $total = 0.0;
        foreach (range(1, random_int(1, 2)) as $ignored) {
            $piece = $this->faker->randomElement(self::PIECES);
            $prix = (float) $piece[1] * $this->faker->randomFloat(2, 0.9, 1.2);
            $prix = round($prix, 2);
            $qte = random_int(1, 2);
            $lines[] = ['designation' => $piece[0], 'prix' => $prix, 'quantite' => $qte];
            $total += $prix * $qte;
        }

        return [$lines, round($total, 2)];
    }

    /** @return array<string, mixed> */
    private function computeBilling(bool $domicile, bool $freeTravel, float $prestations, float $pieces): array
    {
        $km = 0.0;
        $deplacement = 0.0;
        if ($domicile && ! $freeTravel) {
            $km = (float) random_int(4, 45);
            $deplacement = round($km * $this->faker->randomFloat(2, 0.55, 0.85) + 5, 2);
        }

        $remiseType = null;
        $remiseValeur = null;
        $remiseMontant = 0.0;
        $base = $prestations + $pieces + $deplacement;
        if ($this->faker->boolean(12) && $base > 0) {
            if ($this->faker->boolean()) {
                $remiseType = 'pourcent';
                $remiseValeur = $this->faker->randomElement([5, 10, 15]);
                $remiseMontant = round($base * $remiseValeur / 100, 2);
            } else {
                $remiseType = 'euro';
                $remiseValeur = $this->faker->randomElement([5, 10, 20]);
                $remiseMontant = (float) $remiseValeur;
            }
        }

        $total = round(max(0, $base - $remiseMontant), 2);
        $payee = $this->faker->boolean(80);

        return [
            'montant_prestations' => $prestations,
            'montant_pieces' => $pieces,
            'montant_deplacement' => $deplacement ?: null,
            'deplacement_km' => $km ?: null,
            'remise_type' => $remiseType,
            'remise_valeur' => $remiseValeur,
            'remise_montant' => $remiseMontant ?: null,
            'montant_total' => $total,
            'montant_paye' => $payee ? $total : ($this->faker->boolean(30) ? round($total / 2, 2) : 0),
            'payee' => $payee,
            'paiement_mode' => $payee ? $this->faker->randomElement(['espèces', 'cb', 'cheque', 'virement']) : null,
        ];
    }

    /** @param array<int,User> $assigned */
    private function buildLogs(int $id, User $opener, array $assigned, Carbon $openedAt, bool $closed, Carbon $lastActivity): void
    {
        $this->buffers['intervention_logs'][] = [
            'intervention_id' => $id, 'user_id' => $opener->id,
            'texte' => "a créé l'intervention", 'created_at' => $openedAt,
        ];
        $this->buffers['intervention_logs'][] = [
            'intervention_id' => $id, 'user_id' => $assigned[0]->id,
            'texte' => "a pris en charge l'intervention",
            'created_at' => $openedAt->copy()->addMinutes(random_int(5, 240)),
        ];
        if ($this->faker->boolean(60)) {
            $this->buffers['intervention_logs'][] = [
                'intervention_id' => $id, 'user_id' => $assigned[0]->id,
                'texte' => 'a modifié le statut',
                'created_at' => $this->between($openedAt, $lastActivity),
            ];
        }
        if ($closed) {
            $this->buffers['intervention_logs'][] = [
                'intervention_id' => $id, 'user_id' => $assigned[0]->id,
                'texte' => "a clôturé l'intervention", 'created_at' => $lastActivity,
            ];
        }
    }

    /** @param array<int,User> $assigned */
    private function buildChats(int $id, array $assigned, Carbon $openedAt, Carbon $lastActivity): void
    {
        if ($this->faker->boolean(40)) {
            foreach (range(1, random_int(1, 3)) as $ignored) {
                $this->buffers['intervention_messages'][] = [
                    'intervention_id' => $id,
                    'user_id' => $assigned[array_rand($assigned)]->id,
                    'message' => $this->faker->randomElement(self::CHAT_INTERNE),
                    'created_at' => $this->between($openedAt, $lastActivity),
                    'updated_at' => $lastActivity,
                ];
            }
        }
        if ($this->faker->boolean(30)) {
            $when = $openedAt->copy()->addHours(random_int(1, 48));
            $this->buffers['public_messages'][] = [
                'intervention_id' => $id, 'author' => 'client', 'user_id' => null,
                'message' => $this->faker->randomElement(self::PUBLIC_CLIENT),
                'created_at' => $when,
            ];
            $this->buffers['public_messages'][] = [
                'intervention_id' => $id, 'author' => 'staff', 'user_id' => $assigned[0]->id,
                'message' => $this->faker->randomElement(self::PUBLIC_STAFF),
                'created_at' => $when->copy()->addHours(random_int(1, 12)),
            ];
        }
    }

    /** @param array<string,mixed> $recipientMeta */
    private function buildClientMessages(int $id, int $recipientId, User $tech, Carbon $openedAt, Carbon $lastActivity, bool $closed, array $recipientMeta): void
    {
        $dest = $recipientMeta['pro'] ? $this->faker->companyEmail() : $this->faker->mobileNumber();
        if ($closed && $this->faker->boolean(60)) {
            $this->buffers['client_messages'][] = [
                'client_id' => $recipientId, 'intervention_id' => $id, 'user_id' => $tech->id,
                'canal' => 'sms', 'destinataire' => $this->faker->mobileNumber(),
                'sujet' => null, 'corps' => 'Votre matériel est prêt, vous pouvez venir le récupérer. À bientôt.',
                'statut' => 'envoye', 'programme_pour' => null, 'sent_at' => $lastActivity,
                'created_at' => $lastActivity, 'updated_at' => $lastActivity,
            ];
        }
        if ($this->faker->boolean(25)) {
            $when = $openedAt->copy()->addHours(random_int(2, 72));
            $this->buffers['client_messages'][] = [
                'client_id' => $recipientId, 'intervention_id' => $id, 'user_id' => $tech->id,
                'canal' => 'email', 'destinataire' => $dest,
                'sujet' => 'Devis pour votre réparation', 'corps' => 'Bonjour, veuillez trouver ci-joint le devis correspondant à votre intervention. Cordialement.',
                'statut' => 'envoye', 'programme_pour' => null, 'sent_at' => $when,
                'created_at' => $when, 'updated_at' => $when,
            ];
        }
    }

    private function buildOrders(int $id, Carbon $openedAt, bool $closed, Carbon $lastActivity): void
    {
        if ($this->faker->boolean(18)) {
            $commandeLe = $openedAt->copy()->addDays(random_int(0, 5));
            $recue = $closed || $this->faker->boolean(60);
            $this->buffers['commandes'][] = [
                'intervention_id' => $id,
                'fournisseur' => $this->faker->randomElement(self::FOURNISSEURS),
                'bon_commande' => 'BC-'.$this->faker->numerify('#####'),
                'numero_commande' => $this->faker->bothify('CMD-####??'),
                'suivi_colis' => $this->faker->boolean(70) ? strtoupper($this->faker->bothify('??############FR')) : null,
                'commande_le' => $commandeLe->toDateString(),
                'recue_le' => $recue ? $commandeLe->copy()->addDays(random_int(1, 8))->toDateString() : null,
                'recue' => (int) $recue,
                'created_at' => $commandeLe, 'updated_at' => $lastActivity,
            ];
        }
        if ($this->faker->boolean(7)) {
            $envoye = $openedAt->copy()->addDays(random_int(0, 4));
            $retournee = $closed || $this->faker->boolean(50);
            $this->buffers['sous_traitances'][] = [
                'intervention_id' => $id,
                'nom' => $this->faker->randomElement(self::SOUS_TRAITANTS),
                'devis' => $this->faker->boolean(60) ? $this->faker->randomElement([90, 150, 250, 400, 650]).' €' : null,
                'numero_commande' => $this->faker->bothify('ST-####'),
                'suivi_aller' => strtoupper($this->faker->bothify('??############FR')),
                'suivi_retour' => $retournee ? strtoupper($this->faker->bothify('??############FR')) : null,
                'envoye_le' => $envoye->toDateString(),
                'retour_le' => $retournee ? $envoye->copy()->addDays(random_int(3, 15))->toDateString() : null,
                'retournee' => (int) $retournee,
                'created_at' => $envoye, 'updated_at' => $lastActivity,
            ];
        }
    }

    private function buildPhotos(int $id, User $tech, Carbon $openedAt, Carbon $lastActivity): void
    {
        if (! $this->faker->boolean(40)) {
            return;
        }
        foreach (range(1, random_int(1, 3)) as $ignored) {
            $this->buffers['intervention_photos'][] = [
                'intervention_id' => $id, 'user_id' => $tech->id,
                'path' => 'interventions/photos/'.Str::uuid().'.jpg',
                'original_name' => $this->faker->randomElement(['etat_entree', 'carte_mere', 'ecran', 'apres_reparation', 'serie']).'.jpg',
                'prive' => (int) $this->faker->boolean(30),
                'created_at' => $this->between($openedAt, $lastActivity), 'updated_at' => $lastActivity,
            ];
        }
    }

    private function buildSatisfaction(int $id, int $recipientId, Carbon $closedAt): void
    {
        if (! $this->faker->boolean(65)) {
            return;
        }
        $sentAt = $closedAt->copy()->addDays(1);
        $submitted = $this->faker->boolean(75);
        $note = $this->faker->randomElement([5, 5, 5, 4, 4, 4, 3, 2, 1]);
        $this->buffers['satisfactions'][] = [
            'intervention_id' => $id, 'client_id' => $recipientId,
            'token' => Str::random(48),
            'note' => $submitted ? $note : null,
            'commentaire' => $submitted && $this->faker->boolean(45) ? $this->faker->randomElement(self::SATISFACTION_COMMENTS) : null,
            'sent_at' => $sentAt,
            'submitted_at' => $submitted ? $sentAt->copy()->addDays(random_int(0, 6)) : null,
            'created_at' => $sentAt, 'updated_at' => $sentAt,
        ];
    }

    // ------------------------------------------------------------------ Maintenance packs

    /**
     * @param  array{admin: User, pool: array<int,User>}  $techs
     * @param  array<int, array<string,mixed>>  $meta
     */
    private function seedMaintenancePacks(array $techs, array $meta): void
    {
        $rows = [];
        foreach ($meta as $clientId => $m) {
            if (! $m['pro'] || ! $this->faker->boolean(25)) {
                continue;
            }
            $start = $m['created']->copy()->addDays(random_int(0, 60));
            if ($start->greaterThan(now())) {
                $start = now()->copy()->subDay();
            }
            $rows[] = [
                'client_id' => $clientId, 'intervention_id' => null,
                'user_id' => $techs['admin']->id,
                'mouvement' => $this->faker->randomElement([10, 20, 30]),
                'description' => 'Achat pack maintenance',
                'created_at' => $start, 'updated_at' => $start,
            ];
            foreach (range(1, random_int(1, 6)) as $ignored) {
                $when = $this->between($start, now());
                $rows[] = [
                    'client_id' => $clientId, 'intervention_id' => null,
                    'user_id' => $techs['pool'][array_rand($techs['pool'])]->id,
                    'mouvement' => -1 * $this->faker->randomElement([0.5, 1, 1.5, 2]),
                    'description' => 'Consommation maintenance',
                    'created_at' => $when, 'updated_at' => $when,
                ];
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('maintenance_movements')->insert($chunk);
        }
    }

    // ------------------------------------------------------------------ Tasks & events

    /**
     * @param  array{pool: array<int,User>, admin: User}  $techs
     * @param  array{pro: array<int,int>, particulier: array<int,int>}  $clientIds
     */
    private function seedTasksAndEvents(float $scale, array $techs, array $clientIds): void
    {
        $allClients = array_merge($clientIds['pro'], $clientIds['particulier']);
        $assignees = array_merge($techs['pool'], [$techs['admin']]);
        $interventionIds = Intervention::pluck('id')->all();

        $tasks = [];
        $taskCount = (int) round(self::TASKS * $scale);
        for ($i = 0; $i < $taskCount; $i++) {
            $created = Carbon::instance($this->faker->dateTimeBetween('-3 years', 'now'));
            $statut = $this->faker->randomElement(['a_faire', 'a_faire', 'en_cours', 'terminee', 'terminee']);
            $estim = $this->faker->randomElement([0.5, 1, 1, 2, 3, null]);
            $user = $assignees[array_rand($assignees)];
            $tasks[] = [
                'user_id' => $user->id,
                'created_by' => $techs['admin']->id,
                'client_id' => $this->faker->boolean(45) && $allClients ? $allClients[array_rand($allClients)] : null,
                'intervention_id' => $this->faker->boolean(35) && $interventionIds ? $interventionIds[array_rand($interventionIds)] : null,
                'titre' => $this->faker->randomElement(self::TASK_TITLES),
                'description' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
                'statut' => $statut,
                'priorite' => $this->faker->randomElement([0, 0, 1, 2]),
                'heures_estimees' => $estim,
                'heures_passees' => $statut === 'terminee' ? ($estim ? round($estim * $this->faker->randomFloat(2, 0.6, 1.4), 2) : $this->faker->randomElement([0.5, 1, 2])) : null,
                'echeance' => $this->faker->boolean(60) ? $created->copy()->addDays(random_int(1, 30))->toDateString() : null,
                'completed_at' => $statut === 'terminee' ? $created->copy()->addDays(random_int(0, 20)) : null,
                'created_at' => $created, 'updated_at' => $created,
            ];
        }
        foreach (array_chunk($tasks, 500) as $chunk) {
            DB::table('tasks')->insert($chunk);
        }

        $events = [];
        $eventCount = (int) round(self::EVENTS * $scale);
        for ($i = 0; $i < $eventCount; $i++) {
            // Mix of past history and upcoming agenda.
            $debut = $this->faker->boolean(70)
                ? Carbon::instance($this->faker->dateTimeBetween('-3 years', 'now'))
                : now()->copy()->addDays(random_int(0, 45));
            $debut->setTime(random_int(8, 17), $this->faker->randomElement([0, 30]));
            $allDay = $this->faker->boolean(20);
            $user = $assignees[array_rand($assignees)];
            $events[] = [
                'user_id' => $user->id,
                'client_id' => $this->faker->boolean(35) && $allClients ? $allClients[array_rand($allClients)] : null,
                'titre' => $this->faker->randomElement(self::EVENT_TITLES),
                'description' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
                'debut' => $allDay ? $debut->copy()->startOfDay() : $debut,
                'fin' => $allDay ? $debut->copy()->endOfDay() : $debut->copy()->addHours(random_int(1, 4)),
                'journee_entiere' => (int) $allDay,
                'couleur' => $this->faker->randomElement(['#2563eb', '#16a34a', '#f59e0b', '#ef4444', '#a855f7']),
                'created_at' => $debut, 'updated_at' => $debut,
            ];
        }
        foreach (array_chunk($events, 500) as $chunk) {
            DB::table('events')->insert($chunk);
        }
    }

    // ------------------------------------------------------------------ Absences & notes

    /** @param array{pool: array<int,User>, admin: User} $techs */
    private function seedAbsencesAndNotes(array $techs): void
    {
        foreach ($techs['pool'] as $tech) {
            // ~4 years of leave history + some upcoming.
            for ($year = 4; $year >= 0; $year--) {
                $periods = random_int(2, 4);
                for ($p = 0; $p < $periods; $p++) {
                    $debut = now()->copy()->subYears($year)
                        ->setMonth(random_int(1, 12))->setDay(random_int(1, 25))
                        ->setTime(8, 0);
                    if ($debut->isFuture() && $year !== 0) {
                        continue;
                    }
                    $motif = $this->faker->randomElement(['conges', 'conges', 'conges', 'maladie', 'formation', 'autre']);
                    $days = match ($motif) {
                        'conges' => random_int(2, 10),
                        'maladie' => random_int(1, 4),
                        'formation' => random_int(1, 3),
                        default => 1,
                    };
                    TechnicianAbsence::create([
                        'user_id' => $tech->id,
                        'debut' => $debut,
                        'fin' => $debut->copy()->addDays($days)->setTime(18, 0),
                        'journee_entiere' => true,
                        'motif' => $motif,
                        'note' => $this->faker->boolean(30) ? ucfirst($motif).' planifié(e)' : null,
                        'created_by' => $techs['admin']->id,
                        'created_at' => $debut->copy()->subWeeks(random_int(1, 6)),
                        'updated_at' => $debut->copy()->subWeeks(random_int(1, 6)),
                    ]);
                }
            }

            // A couple of personal sticky notes.
            foreach (range(1, random_int(1, 3)) as $ordre) {
                DB::table('sticky_notes')->insert([
                    'user_id' => $tech->id,
                    'contenu' => $this->faker->randomElement(self::STICKY_NOTES),
                    'couleur' => $this->faker->randomElement(['#fde68a', '#bfdbfe', '#bbf7d0', '#fecaca', '#e9d5ff']),
                    'ordre' => $ordre,
                    'created_at' => now()->subDays(random_int(1, 200)),
                    'updated_at' => now()->subDays(random_int(0, 30)),
                ]);
            }
        }
    }

    // ------------------------------------------------------------------ Helpers

    private function between(Carbon $start, Carbon $end): Carbon
    {
        if ($end->lessThanOrEqualTo($start)) {
            return $start->copy();
        }

        return Carbon::instance($this->faker->dateTimeBetween($start, $end));
    }

    private function flush(): void
    {
        foreach ($this->buffers as $table => $rows) {
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table($table)->insert($chunk);
            }
        }
        $this->buffers = [];
    }

    // ------------------------------------------------------------------ Content pools

    private const PANNES = [
        'PC très lent au démarrage, mises à jour qui tournent en boucle.',
        'Écran bleu (BSOD) récurrent depuis quelques jours.',
        "L'ordinateur ne s'allume plus du tout.",
        'Plus de connexion Internet sur le poste.',
        'Nombreux pop-ups publicitaires, navigateur détourné.',
        'Disque dur qui fait du bruit, fortes lenteurs.',
        'La boîte mail ne reçoit plus les messages.',
        'Imprimante non reconnue après mise à jour Windows.',
        'Mot de passe Windows oublié, session inaccessible.',
        'Récupération de photos sur un disque externe défaillant.',
        'Wifi instable dans les bureaux en fin de matinée.',
        'NAS inaccessible depuis le réseau.',
        'Migration des données vers un nouveau poste.',
        'Installation et paramétrage du logiciel de comptabilité.',
        'Écran de PC portable cassé à remplacer.',
        'Batterie de portable qui ne tient plus la charge.',
        'Surchauffe et arrêts intempestifs en pleine utilisation.',
        'Demande de sauvegarde automatique des données.',
        'Poste infecté par un rançongiciel, fichiers chiffrés.',
        'Lenteurs Office et Outlook qui se fige.',
    ];

    private const DIAGNOSTICS = [
        'Nettoyage complet + remplacement de la pâte thermique. Tests OK.',
        'Réinstallation propre de Windows et restauration des données. RAS.',
        'Disque remplacé par un SSD, clonage du système. Performances rétablies.',
        'Suppression des malwares (Malwarebytes + ESET). Système assaini.',
        'Alimentation HS remplacée. Poste de nouveau fonctionnel.',
        'Configuration de la messagerie en IMAP. Réception/Envoi OK.',
        'Pilotes imprimante réinstallés, impression de test validée.',
        'Barrette RAM défectueuse remplacée. Plus de BSOD constaté.',
        "Point d'accès Wifi remplacé et reconfiguré. Couverture OK.",
        "Écran remplacé, dalle d'origine. Affichage parfait.",
        'Batterie remplacée, autonomie nominale rétablie.',
        'Sauvegarde planifiée mise en place sur disque externe + cloud.',
        'Récupération de 95% des données via station de clonage.',
        'Mise à jour BIOS + nettoyage ventilation. Températures normales.',
    ];

    private const MESSAGES_CLIENT = [
        'Votre matériel est prêt, tout fonctionne correctement.',
        'Intervention terminée, pensez à effectuer vos sauvegardes régulièrement.',
        'Réparation effectuée, nous restons disponibles en cas de besoin.',
        'Appareil testé et fonctionnel, bonne réception.',
    ];

    private const MATERIELS_DEPOSES = [
        'PC portable + chargeur',
        'Unité centrale seule',
        'Unité centrale + câble alimentation',
        'PC portable HP + chargeur + sacoche',
        'Disque dur externe 1To',
        'Imprimante multifonction',
        'Tour + écran + clavier/souris',
        'MacBook + chargeur MagSafe',
    ];

    private const PIECES = [
        ['SSD 1 To', 79], ['SSD 500 Go', 55], ['Barrette RAM 8 Go DDR4', 35],
        ['Barrette RAM 16 Go DDR4', 60], ['Alimentation 500W', 49], ['Ventilateur CPU', 25],
        ['Écran 15.6" portable', 89], ['Batterie portable', 65], ['Clavier portable', 39],
        ['Disque dur 2 To', 69], ['Carte graphique entrée de gamme', 120], ['Pâte thermique', 9],
        ['Câble HDMI', 12], ['Onduleur 650VA', 79], ['Cartouche toner', 59],
    ];

    private const FOURNISSEURS = [
        'LDLC Pro', 'Materiel.net', 'GrosBill', 'TD SYNNEX', 'Ingram Micro',
        'Rexel', 'Amazon Business', 'Top Achat', 'Inmac Wstore', 'CDiscount Pro',
    ];

    private const SOUS_TRAITANTS = [
        'DataLabo Récupération', 'MicroSoudure 67', 'Atelier Réparation Écrans',
        'ServerExpert', 'PrintFix Services', 'Recovery Lab France',
    ];

    private const CHAT_INTERNE = [
        'Je prends en charge ce poste cet après-midi.',
        'Pièce commandée, réception prévue jeudi.',
        'Client rappelé, il valide le devis.',
        'Attention : données importantes à sauvegarder avant réinstall.',
        'Terminé de mon côté, prêt à restituer.',
        'Le disque est vraiment en fin de vie, je propose un SSD.',
        "J'ai lancé le test mémoire, je regarde demain matin.",
        'Penser à facturer le déplacement sur ce dossier.',
    ];

    private const PUBLIC_CLIENT = [
        'Bonjour, avez-vous du nouveau sur ma réparation ?',
        'Merci pour le suivi, je passe demain en fin de journée.',
        "D'accord pour le devis, vous pouvez lancer la réparation.",
        'Est-ce que mes données seront bien conservées ?',
        'Super, merci beaucoup pour votre réactivité !',
    ];

    private const PUBLIC_STAFF = [
        'Bonjour, le diagnostic est en cours, nous revenons vers vous rapidement.',
        'Votre appareil est prêt, vous pouvez venir le récupérer.',
        'Nous attendons une pièce, livraison sous 48h.',
        'Devis envoyé par e-mail, en attente de votre validation.',
        'Données sauvegardées, aucune perte à signaler.',
    ];

    private const SATISFACTION_COMMENTS = [
        'Très bon service, rapide et efficace.',
        "Technicien à l'écoute, je recommande vivement.",
        'Délai un peu long mais travail soigné.',
        'Parfait, comme toujours !',
        'Accueil sympathique, problème résolu du premier coup.',
        'Prix correct et explications claires.',
        'Un peu cher mais matériel comme neuf.',
        'Réactivité au top, merci !',
    ];

    private const TASK_TITLES = [
        'Rappeler le client pour le devis',
        'Commander un SSD 1 To',
        'Préparer le poste de prêt',
        'Sauvegarder les données du serveur',
        'Faire le point sur le stock de pièces',
        'Relancer le fournisseur pour la livraison',
        'Configurer la nouvelle imprimante réseau',
        'Établir la facture du mois',
        'Mettre à jour les antivirus du parc',
        'Planifier la maintenance trimestrielle',
    ];

    private const EVENT_TITLES = [
        'RDV sur site - maintenance',
        'Livraison de matériel',
        'Formation interne',
        "Réunion d'équipe",
        'Inventaire atelier',
        'Installation client',
        'Récupération de poste',
    ];

    private const STICKY_NOTES = [
        'Penser à relancer le fournisseur LDLC',
        'Clé de licence Office dans le coffre',
        'Commander des câbles HDMI',
        'Facturer les déplacements de la semaine',
        'Sauvegarde NAS à vérifier le vendredi',
        'Rappeler M. Durand avant 17h',
    ];
}
