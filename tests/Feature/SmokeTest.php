<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Intervention;
use App\Models\Satisfaction;
use App\Models\User;
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

    public function test_guest_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertOk();
    }

    public function test_admin_can_browse_main_pages(): void
    {
        $this->actingAs($this->admin());

        foreach ([
            '/', '/clients', '/clients/create', '/interventions', '/interventions/create',
            '/calendrier', '/tasks', '/maintenance', '/statistiques', '/journaux',
            '/satisfaction', '/staff', '/staff/create', '/automatismes', '/automatismes/create',
            '/facturation', '/parametres', '/profil', '/recherche?q=dup',
        ] as $url) {
            $this->get($url)->assertOk();
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

    public function test_maintenance_credit_then_debit_on_close(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $intervention = Intervention::ouvertes()->first();
        $client = $intervention->client;

        // Credit 10h
        $this->post(route('maintenance.store', $client), ['sens' => 'credit', 'heures' => 10])->assertRedirect();
        $this->assertEqualsWithDelta(10.0, $client->soldeMaintenance(), 0.001);

        // Add a 2h prestation then close -> pack debited by the total logged hours.
        $intervention->prestations()->create(['designation' => 'Test', 'duree' => 2]);
        $total = (float) $intervention->prestations()->sum('duree');
        $this->post(route('interventions.restituer', $intervention))->assertRedirect();
        $this->assertEqualsWithDelta(10.0 - $total, $client->fresh()->soldeMaintenance(), 0.001);
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
