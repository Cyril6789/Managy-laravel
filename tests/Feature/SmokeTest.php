<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Intervention;
use App\Models\Satisfaction;
use App\Models\Setting;
use App\Models\User;
use App\Support\Billing;
use App\Support\Deplacement;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SmokeTest extends TestCase
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

    public function test_guest_sees_landing_and_login(): void
    {
        // Guests land on the SaaS marketing page; the app itself stays protected.
        $this->get('/')->assertOk()->assertSee('Managy');
        $this->get('/login')->assertOk();
        $this->get('/tableau-de-bord')->assertRedirect('/login');
    }

    public function test_admin_can_browse_main_pages(): void
    {
        $this->actingAs($this->admin());

        foreach ([
            '/tableau-de-bord', '/clients', '/clients/create', '/interventions', '/interventions/create',
            '/calendrier', '/disponibilites', '/tasks', '/maintenance', '/statistiques', '/journaux',
            '/satisfaction', '/staff', '/staff/create', '/automatismes', '/automatismes/create',
            '/facturation', '/parametres', '/profil', '/recherche?q=dup',
        ] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Vite manifest', false);
        }
    }

    public function test_admin_can_view_records(): void
    {
        $this->actingAs($this->admin());

        $client = Client::first();
        $this->get(route('clients.show', $client))->assertOk();
        $this->get(route('clients.edit', $client))->assertOk();

        $intervention = Intervention::first();
        $this->get(route('interventions.show', $intervention))->assertOk();
        $this->get(route('interventions.edit', $intervention))->assertOk();
    }

    public function test_intervention_print_sheets_render(): void
    {
        $this->actingAs($this->admin());
        $intervention = Intervention::first();

        $this->get(route('interventions.print', [$intervention, 'depot']))->assertOk()->assertSee('Dépôt');
        $this->get(route('interventions.print', [$intervention, 'rapport']))->assertOk()->assertSee('Rapport');
    }

    public function test_client_json_endpoints(): void
    {
        $this->actingAs($this->admin());

        $this->getJson('/clients/recherche?q=du')->assertOk();
        $this->postJson('/clients/rapide', ['nom' => 'Client Test API'])
            ->assertOk()->assertJsonStructure(['id', 'label']);

        $client = Client::first();
        $this->getJson(route('interventions.client_context', $client))
            ->assertOk()->assertJsonStructure(['maintenance' => ['has', 'balance', 'threshold', 'low'], 'materiels', 'pannes', 'notes']);
    }

    public function test_save_report_and_assign(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $intervention = Intervention::ouvertes()->first();

        $this->patch(route('interventions.rapport', $intervention), ['diagnostic' => 'Rapport en cours'])
            ->assertRedirect();
        $this->assertSame('Rapport en cours', $intervention->fresh()->diagnostic);

        $tech = User::where('pseudo', 'tech')->first();
        $this->post(route('interventions.assign', $intervention), ['user_id' => $tech->id, 'action' => 'add'])->assertRedirect();
        $this->assertTrue($intervention->techniciens()->where('users.id', $tech->id)->exists());
    }

    public function test_maintenance_pack_debit_is_opt_in_on_close(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $intervention = Intervention::ouvertes()->first();
        $client = $intervention->client;

        // Credit 10h
        $this->post(route('maintenance.store', $client), ['sens' => 'credit', 'heures' => 10])->assertRedirect();
        $this->assertEqualsWithDelta(10.0, $client->soldeMaintenance(), 0.001);

        // Closing WITHOUT requesting a pack deduction leaves the pack untouched
        // (the customer pays everything in money).
        $intervention->prestations()->create(['designation' => 'Test', 'duree' => 2]);
        $this->post(route('interventions.restituer', $intervention))->assertRedirect();
        $this->assertEqualsWithDelta(10.0, $client->fresh()->soldeMaintenance(), 0.001);
    }

    public function test_maintenance_pack_settles_part_of_the_service_hours(): void
    {
        $this->actingAs($this->admin());

        $intervention = Intervention::ouvertes()->first();
        $intervention->update(['type_lieu' => 'domicile']);
        $client = $intervention->client;
        $client->update(['remise_prestations' => 0, 'remise_pieces' => 0]);

        // Pack with 1.5h available; the job logs 2h of service at 100 €/h.
        $this->post(route('maintenance.store', $client), ['sens' => 'credit', 'heures' => 1.5])->assertRedirect();
        $intervention->prestations()->delete();
        $intervention->prestations()->create(['designation' => 'Prestation', 'duree' => 2, 'tarif' => 100]);

        // Ask to settle 1.5h from the pack -> 150 € covered, 50 € left to pay.
        $this->post(route('interventions.restituer', $intervention), [
            'maintenance_heures' => 1.5,
            'montant_deplacement' => 0,
        ])->assertRedirect();

        $intervention->refresh();
        $this->assertEqualsWithDelta(1.5, (float) $intervention->maintenance_heures, 0.001);
        $this->assertEqualsWithDelta(150.0, (float) $intervention->montant_maintenance, 0.01);
        $this->assertEqualsWithDelta(50.0, (float) $intervention->montant_total, 0.01);
        // The pack is debited by exactly the requested (available) hours.
        $this->assertEqualsWithDelta(0.0, $client->fresh()->soldeMaintenance(), 0.001);
    }

    public function test_maintenance_pack_deduction_is_capped_at_balance(): void
    {
        $this->actingAs($this->admin());

        $intervention = Intervention::ouvertes()->first();
        $intervention->update(['type_lieu' => 'domicile']);
        $client = $intervention->client;

        // Only 1h available but 3h requested on a 2h job -> capped at 1h.
        $this->post(route('maintenance.store', $client), ['sens' => 'credit', 'heures' => 1])->assertRedirect();
        $intervention->prestations()->delete();
        $intervention->prestations()->create(['designation' => 'Prestation', 'duree' => 2, 'tarif' => 100]);

        $this->post(route('interventions.restituer', $intervention), [
            'maintenance_heures' => 3,
            'montant_deplacement' => 0,
        ])->assertRedirect();

        $intervention->refresh();
        $this->assertEqualsWithDelta(1.0, (float) $intervention->maintenance_heures, 0.001);
        $this->assertEqualsWithDelta(0.0, $client->fresh()->soldeMaintenance(), 0.001);
    }

    public function test_sms_goes_to_the_selected_contact(): void
    {
        $this->actingAs($this->admin());

        $company = Client::create(['type' => 'professionnel', 'nom' => 'ACME SARL', 'telephone_fixe' => '0388000000']);
        $contact = Client::create(['type' => 'particulier', 'parent_id' => $company->id, 'nom' => 'Durand', 'prenom' => 'Léa', 'telephone_mobile' => '0699999999']);

        $intervention = Intervention::create([
            'client_id' => $company->id,
            'contact_id' => $contact->id,
            'opened_by' => $this->admin()->id,
        ]);

        $this->post(route('interventions.message_client', $intervention), [
            'canal' => 'sms', 'corps' => 'Votre matériel est prêt.',
        ])->assertRedirect();

        $this->assertDatabaseHas('client_messages', [
            'intervention_id' => $intervention->id,
            'client_id' => $company->id,        // logged against the company
            'destinataire' => '0699999999',     // but sent to the contact
        ]);
    }

    public function test_company_has_no_first_name(): void
    {
        $company = Client::create(['type' => 'professionnel', 'nom' => 'Société X', 'prenom' => 'Ignoré']);
        $this->assertNull($company->fresh()->prenom);
    }

    public function test_restitution_stores_signature(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $intervention = Intervention::ouvertes()->first();

        $png = 'data:image/png;base64,'.base64_encode(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));

        $this->post(route('interventions.restituer', $intervention), [
            'signataire_nom' => 'Jean Client',
            'signature' => $png,
        ])->assertRedirect();

        $intervention->refresh();
        $this->assertNotNull($intervention->signature_path);
        $this->assertSame('Jean Client', $intervention->signataire_nom);
        $this->assertNotNull($intervention->signed_at);
        Storage::disk('public')->assertExists($intervention->signature_path);
    }

    public function test_finalisation_then_restitution_workflow(): void
    {
        $this->actingAs($this->admin());
        $intervention = Intervention::ouvertes()->first();
        $intervention->update(['type_lieu' => 'atelier', 'finalisee_at' => null]);

        // Workshop: mark as finalised (unlocks "restituer & clôturer").
        $this->post(route('interventions.finaliser', $intervention))->assertRedirect();
        $this->assertNotNull($intervention->fresh()->finalisee_at);

        // Restituting from the modal with the "facturée" box ticked.
        $this->post(route('interventions.restituer', $intervention), ['facturee' => 1])->assertRedirect();
        $intervention->refresh();
        $this->assertTrue($intervention->estCloturee());
        $this->assertTrue($intervention->facturee);
    }

    public function test_domicile_restitution_records_travel_and_payment(): void
    {
        Setting::put('deplacement_mode', 'forfait');
        Setting::put('deplacement_forfait', '30');

        $this->actingAs($this->admin());
        $intervention = Intervention::ouvertes()->first();
        $intervention->update(['type_lieu' => 'domicile']);
        $intervention->prestations()->create(['designation' => 'Diagnostic', 'duree' => 1, 'tarif' => 50]);

        $this->post(route('interventions.restituer', $intervention), [
            'montant_prestations' => 50,
            'montant_deplacement' => 30,
            'montant_total' => 80,
            'payee' => 1,
            'montant_paye' => 80,
            'paiement_mode' => 'cb',
        ])->assertRedirect();

        $intervention->refresh();
        $this->assertTrue($intervention->estCloturee());
        $this->assertEqualsWithDelta(30.0, (float) $intervention->montant_deplacement, 0.001);
        $this->assertEqualsWithDelta(80.0, (float) $intervention->montant_total, 0.001);
        $this->assertTrue($intervention->payee);
        $this->assertSame('cb', $intervention->paiement_mode);
        // Unpaid domicile jobs land in the "à facturer" list (facturee stays false).
        $this->assertFalse($intervention->facturee);
    }

    public function test_billing_applies_client_discounts_and_parts(): void
    {
        $this->actingAs($this->admin());
        $intervention = Intervention::ouvertes()->first();
        $intervention->update(['type_lieu' => 'atelier']);
        $intervention->client->update(['remise_prestations' => 10, 'remise_pieces' => 5]);
        $intervention->prestations()->create(['designation' => 'Prestation', 'duree' => 1, 'tarif' => 100]);
        $intervention->pieces()->create(['designation' => 'SSD', 'prix' => 200, 'quantite' => 1]);

        $b = Billing::compute($intervention->fresh()->load(['prestations', 'pieces', 'client']), 0.0);

        $this->assertEqualsWithDelta(90.0, $b['prestations_net'], 0.01);  // 100 − 10 %
        $this->assertEqualsWithDelta(190.0, $b['pieces_net'], 0.01);      // 200 − 5 %
        $this->assertEqualsWithDelta(280.0, $b['total'], 0.01);
    }

    public function test_ristourne_applied_at_restitution(): void
    {
        $this->actingAs($this->admin()); // admin bypasses the ristourne gate
        $intervention = Intervention::ouvertes()->first();
        // Ristourne only applies on-site (domicile), never in the workshop.
        $intervention->update(['type_lieu' => 'domicile']);
        $intervention->client->update(['remise_prestations' => 0, 'remise_pieces' => 0]);
        $intervention->prestations()->create(['designation' => 'Prestation', 'duree' => 1, 'tarif' => 100]);

        $this->post(route('interventions.restituer', $intervention), [
            'remise_type' => 'pourcent', 'remise_valeur' => 10,
            'montant_deplacement' => 0,
        ])->assertRedirect();

        $intervention->refresh();
        $this->assertSame('pourcent', $intervention->remise_type);
        $this->assertEqualsWithDelta(10.0, (float) $intervention->remise_montant, 0.01);
        $this->assertEqualsWithDelta(90.0, (float) $intervention->montant_total, 0.01);
    }

    public function test_client_free_travel_overrides_fee(): void
    {
        Setting::put('deplacement_mode', 'forfait');
        Setting::put('deplacement_forfait', '40');

        $this->assertEqualsWithDelta(40.0, Deplacement::montant('Paris', null, false), 0.001);
        $this->assertEqualsWithDelta(0.0, Deplacement::montant('Paris', null, true), 0.001);
    }

    public function test_deplacement_free_city_overrides_forfait(): void
    {
        Setting::put('deplacement_mode', 'forfait');
        Setting::put('deplacement_forfait', '40');
        Setting::put('deplacement_villes_gratuites', "Lyon\nVilleurbanne");

        $this->assertEqualsWithDelta(40.0, Deplacement::montant('Paris'), 0.001);
        $this->assertEqualsWithDelta(0.0, Deplacement::montant('lyon'), 0.001);
        $this->assertTrue(Deplacement::villeEstGratuite('VILLEURBANNE'));
    }

    public function test_billing_settings_can_be_saved(): void
    {
        $this->actingAs($this->admin());

        $this->put(route('settings.billing'), [
            'deplacement_mode' => 'km',
            'deplacement_prix_km' => '0.5',
            'deplacement_villes_gratuites' => 'Lyon',
        ])->assertRedirect();

        $this->assertSame('km', Setting::get('deplacement_mode'));
        $this->assertEqualsWithDelta(0.5, Deplacement::prixKm(), 0.001);
    }

    public function test_public_intervention_link_is_accessible(): void
    {
        $intervention = Intervention::first();
        $this->get(route('public.intervention', $intervention->public_token))->assertOk();
    }

    public function test_public_satisfaction_link_is_accessible(): void
    {
        $intervention = Intervention::first();
        $satisfaction = Satisfaction::create([
            'intervention_id' => $intervention->id,
            'client_id' => $intervention->client_id,
            'sent_at' => now(),
        ]);
        $this->get(route('public.satisfaction', $satisfaction->token))->assertOk();
    }
}
