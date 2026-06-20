<?php

namespace Tests\Feature;

use App\Livewire\PendingCommandes;
use App\Livewire\PendingSousTraitances;
use App\Models\Intervention;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReceptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function admin(): User
    {
        return User::where('pseudo', 'admin')->firstOrFail();
    }

    public function test_receiving_an_order_unblocks_and_notifies_the_technician(): void
    {
        $tech = User::where('pseudo', 'tech')->firstOrFail();
        $intervention = Intervention::ouvertes()->first();
        $intervention->update(['opened_by' => $tech->id]);
        $intervention->techniciens()->syncWithoutDetaching([$tech->id => ['assigned_at' => now()]]);
        $commande = $intervention->commandes()->create(['fournisseur' => 'LDLC', 'numero_commande' => 'A1', 'recue' => false]);

        $this->actingAs($this->admin()); // admin bypasses the permission gate

        Livewire::test(PendingCommandes::class)
            ->call('receive', $commande->id);

        $this->assertTrue($commande->fresh()->recue);
        $this->assertNotNull($commande->fresh()->recue_le);
        // The technician in charge is notified, even though someone else received it.
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $tech->id,
            'intervention_id' => $intervention->id,
        ]);
    }

    public function test_receiving_a_subcontracting_return_notifies_the_technician(): void
    {
        $tech = User::where('pseudo', 'tech')->firstOrFail();
        $intervention = Intervention::ouvertes()->first();
        $intervention->update(['opened_by' => $tech->id]);
        $intervention->techniciens()->syncWithoutDetaching([$tech->id => ['assigned_at' => now()]]);
        $sst = $intervention->sousTraitances()->create(['nom' => 'Labo X', 'retournee' => false]);

        $this->actingAs($this->admin());

        Livewire::test(PendingSousTraitances::class)
            ->call('markReturned', $sst->id);

        $this->assertTrue($sst->fresh()->retournee);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $tech->id,
            'intervention_id' => $intervention->id,
        ]);
    }

    public function test_reception_pages_require_the_permission(): void
    {
        // A fresh active user without any granted permission.
        $user = User::create([
            'nom' => 'Sans', 'prenom' => 'Droit', 'pseudo' => 'nodroit',
            'email' => 'nodroit@example.test', 'password' => bcrypt('secret123'),
            'is_admin' => false, 'is_active' => true,
        ]);
        $this->actingAs($user);

        $this->get(route('reception.commandes'))->assertForbidden();
        $this->get(route('reception.sous_traitances'))->assertForbidden();
    }

    public function test_permission_catalog_exposes_reception_rights(): void
    {
        $this->assertContains(Permissions::COMMANDES_RECEPTION, Permissions::all());
        $this->assertContains(Permissions::SOUS_TRAITANCES_RECEPTION, Permissions::all());
    }
}
