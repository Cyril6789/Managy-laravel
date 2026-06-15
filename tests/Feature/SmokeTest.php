<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Intervention;
use App\Models\Satisfaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            '/parametres', '/profil', '/recherche?q=dup',
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
