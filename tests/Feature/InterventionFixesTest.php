<?php

namespace Tests\Feature;

use App\Livewire\InterventionSchedule;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\Notification;
use App\Models\Statut;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InterventionFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('pseudo', 'admin')->firstOrFail());
    }

    public function test_creator_is_not_in_charge_and_intervention_stays_new(): void
    {
        $client = Client::first();
        $defaut = Statut::where('est_defaut', true)->value('id');

        $this->post(route('interventions.store'), [
            'client_id' => $client->id,
            'type_lieu' => 'atelier',
            'statut_id' => $defaut,
        ])->assertRedirect();

        $intervention = Intervention::latest('id')->first();
        $this->assertCount(0, $intervention->techniciens);   // creator not auto-assigned
        $this->assertSame($defaut, $intervention->statut_id); // stays "nouvelle"
    }

    public function test_selected_technicians_are_assigned_at_creation(): void
    {
        $client = Client::first();
        $tech = User::where('pseudo', 'tech')->firstOrFail();

        $this->post(route('interventions.store'), [
            'client_id' => $client->id,
            'type_lieu' => 'domicile',
            'technicien_ids' => [$tech->id],
        ])->assertRedirect();

        $intervention = Intervention::latest('id')->first();
        $this->assertTrue($intervention->techniciens()->where('users.id', $tech->id)->exists());
    }

    public function test_cannot_finalise_with_pending_order(): void
    {
        $intervention = Intervention::ouvertes()->first();
        $intervention->update(['type_lieu' => 'atelier', 'finalisee_at' => null]);
        $intervention->commandes()->create(['fournisseur' => 'LDLC', 'recue' => false]);

        $this->post(route('interventions.finaliser', $intervention))->assertRedirect();
        $this->assertNull($intervention->fresh()->finalisee_at);
    }

    public function test_cannot_close_with_pending_subcontracting(): void
    {
        $intervention = Intervention::ouvertes()->first();
        $intervention->sousTraitances()->create(['nom' => 'Atelier X', 'retournee' => false]);

        $this->post(route('interventions.restituer', $intervention))->assertRedirect();
        $this->assertFalse($intervention->fresh()->estCloturee());
    }

    public function test_warranty_zeroes_billing_and_keeps_maintenance_pack(): void
    {
        $intervention = Intervention::ouvertes()->first();
        $intervention->update(['type_lieu' => 'atelier', 'garantie' => true]);
        $client = $intervention->client;
        $client->maintenanceMovements()->create(['mouvement' => 10, 'description' => 'Pack']);
        $intervention->prestations()->create(['designation' => 'Réparation', 'duree' => 2, 'tarif' => 100]);

        $this->post(route('interventions.restituer', $intervention))->assertRedirect();

        $intervention->refresh();
        $this->assertEqualsWithDelta(0.0, (float) $intervention->montant_total, 0.01);
        $this->assertEqualsWithDelta(0.0, (float) $intervention->montant_prestations, 0.01);
        // Nothing debited from the maintenance pack under warranty.
        $this->assertEqualsWithDelta(10.0, $client->fresh()->soldeMaintenance(), 0.01);
    }

    public function test_prestations_are_billed_hourly_rate_times_duration(): void
    {
        $intervention = Intervention::ouvertes()->first();
        $intervention->update(['type_lieu' => 'atelier', 'finalisee_at' => now(), 'garantie' => false]);
        $intervention->client->update(['remise_prestations' => 0, 'remise_pieces' => 0]);

        // 60 €/h × 1,5 h = 90 € ; 80 €/h × 0,5 h = 40 € → 130 €.
        $intervention->prestations()->create(['designation' => 'Diagnostic', 'duree' => 1.5, 'tarif' => 60]);
        $intervention->prestations()->create(['designation' => 'Nettoyage', 'duree' => 0.5, 'tarif' => 80]);

        $this->assertEqualsWithDelta(130.0, $intervention->fresh()->montantPrestations(), 0.01);

        $this->post(route('interventions.restituer', $intervention))->assertRedirect();

        $this->assertEqualsWithDelta(130.0, (float) $intervention->fresh()->montant_total, 0.01);
    }

    public function test_ristourne_is_ignored_on_workshop_jobs(): void
    {
        $intervention = Intervention::ouvertes()->first();
        $intervention->update(['type_lieu' => 'atelier', 'finalisee_at' => now()]);
        $intervention->client->update(['remise_prestations' => 0, 'remise_pieces' => 0]);
        $intervention->prestations()->create(['designation' => 'Prestation', 'duree' => 1, 'tarif' => 100]);

        $this->post(route('interventions.restituer', $intervention), [
            'remise_type' => 'pourcent', 'remise_valeur' => 10,
        ])->assertRedirect();

        $intervention->refresh();
        $this->assertNull($intervention->remise_type);
        $this->assertEqualsWithDelta(100.0, (float) $intervention->montant_total, 0.01);
    }

    public function test_clicking_a_notification_marks_it_read(): void
    {
        $admin = User::where('pseudo', 'admin')->firstOrFail();
        $n = Notification::create([
            'user_id' => $admin->id,
            'titre' => 'Test',
            'url' => route('dashboard'),
        ]);

        $this->get(route('notifications.read', $n))->assertRedirect(route('dashboard'));
        $this->assertNotNull($n->fresh()->read_at);
    }

    public function test_particulier_has_no_siret_and_can_belong_to_several_companies(): void
    {
        $p = Client::create(['type' => 'particulier', 'nom' => 'Martin', 'siret' => '123']);
        $this->assertNull($p->fresh()->siret);

        $a = Client::create(['type' => 'professionnel', 'nom' => 'Alpha']);
        $b = Client::create(['type' => 'professionnel', 'nom' => 'Beta']);
        $p->companies()->sync([$a->id, $b->id]);

        $this->assertCount(2, $p->fresh()->companies);
    }

    public function test_schedule_component_updates_rdv_and_technicians(): void
    {
        $intervention = Intervention::ouvertes()->first();
        $tech = User::where('pseudo', 'tech')->firstOrFail();
        $intervention->techniciens()->detach(); // start unassigned

        Livewire::test(InterventionSchedule::class, ['mode' => 'live', 'intervention' => $intervention])
            ->set('rdv_debut', '2026-07-01T09:00')
            ->set('rdv_fin', '2026-07-01T11:00')
            ->call('toggleTechnician', $tech->id)
            ->call('save')
            ->assertDispatched('schedule-saved');

        $intervention->refresh();
        $this->assertNotNull($intervention->rdv_debut);
        $this->assertTrue($intervention->techniciens()->where('users.id', $tech->id)->exists());
    }
}
