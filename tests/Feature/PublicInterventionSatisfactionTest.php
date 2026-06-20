<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Intervention;
use App\Models\Satisfaction;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicInterventionSatisfactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function intervention(bool $closed): Intervention
    {
        $client = Client::create(['type' => 'particulier', 'nom' => 'Test', 'prenom' => 'Client']);

        return Intervention::create([
            'reference' => 'T-'.Str::random(4),
            'client_id' => $client->id,
            'public_token' => Str::random(40),
            'opened_at' => now()->subDay(),
            'closed_at' => $closed ? now() : null,
        ]);
    }

    public function test_closed_intervention_offers_satisfaction_and_hides_chat(): void
    {
        $i = $this->intervention(closed: true);

        $response = $this->get("/suivi/{$i->public_token}")->assertOk();

        // A satisfaction survey is created and linked.
        $satisfaction = Satisfaction::where('intervention_id', $i->id)->firstOrFail();

        $response->assertSee('Donner mon avis');
        $response->assertSee(route('public.satisfaction', $satisfaction->token), false);
        // Chat is gone once closed.
        $response->assertDontSee('Échangez avec nous');
    }

    public function test_open_intervention_shows_chat_not_satisfaction(): void
    {
        $i = $this->intervention(closed: false);

        $this->get("/suivi/{$i->public_token}")
            ->assertOk()
            ->assertSee('Échangez avec nous')
            ->assertDontSee('Donner mon avis');

        $this->assertDatabaseMissing('satisfactions', ['intervention_id' => $i->id]);
    }
}
