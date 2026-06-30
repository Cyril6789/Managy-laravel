<?php

namespace Database\Seeders;

use App\Models\Society;
use App\Models\User;
use App\Services\SocietyProvisioner;
use App\Support\Tenancy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Platform super-admin — created first so it owns user id 1.
        //    It has no société and supervises every space from /admin.
        $this->seedSuperAdmin();

        // 2. A ready-to-use demo société + sample data, in every environment.
        //    Idempotent, so re-running db:seed never duplicates anything.
        $demo = $this->seedDemoSociety();
        app(Tenancy::class)->forSociety($demo->id, fn () => $this->call(DemoSeeder::class));
    }

    private function seedSuperAdmin(): void
    {
        $email = config('saas.super_admin.email');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'nom' => config('saas.super_admin.name'),
                'password' => Hash::make(config('saas.super_admin.password')),
                'is_super_admin' => true,
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $this->command?->info($user->wasRecentlyCreated
            ? "Super-admin créé : {$email} (pensez à changer le mot de passe)."
            : "Super-admin déjà présent : {$email}.");
    }

    private function seedDemoSociety(): Society
    {
        $society = Society::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Atelier Démo Informatique',
                'email' => 'admin@exemple.fr',
                'phone' => '0388000000',
                'city' => 'Strasbourg',
                'is_active' => true,
            ],
        );

        // Seed the société's reference data (statuses, OS, antivirus, ...).
        app(SocietyProvisioner::class)->provision($society);

        // The société's gérant. pseudo 'admin' keeps the demo data seeder happy.
        app(Tenancy::class)->forSociety($society->id, fn () => User::firstOrCreate(
            ['email' => 'admin@exemple.fr'],
            [
                'pseudo' => 'admin',
                'prenom' => 'Admin',
                'nom' => 'Managy',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ));

        return $society;
    }
}
