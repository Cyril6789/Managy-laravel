<?php

namespace Tests\Feature;

use App\Models\Automatisme;
use App\Models\Client;
use App\Models\ClientMessage;
use App\Models\Intervention;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduledAutomatismeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_appointment_reminder_fires_once(): void
    {
        $client = Client::create(['type' => 'particulier', 'nom' => 'Test', 'telephone_mobile' => '0600000000']);

        Automatisme::create([
            'libelle' => 'Rappel 15 min avant',
            'evenement' => 'rendez_vous',
            'offset_minutes' => -15,
            'type_lieu' => 'domicile',
            'canal' => 'sms',
            'modele' => 'Un technicien arrive chez vous vers {heure_rdv}.',
            'actif' => true,
        ]);

        // Appointment in 10 min at home -> target (rdv - 15) is in the past window.
        $intervention = Intervention::create([
            'client_id' => $client->id,
            'type_lieu' => 'domicile',
            'rdv_debut' => now()->addMinutes(10),
        ]);

        $this->artisan('managy:run-automatismes')->assertSuccessful();

        $this->assertDatabaseHas('client_messages', ['intervention_id' => $intervention->id, 'canal' => 'sms']);
        $this->assertDatabaseHas('automatisme_runs', ['intervention_id' => $intervention->id]);

        // Running again must not send a second time.
        $this->artisan('managy:run-automatismes')->assertSuccessful();
        $this->assertSame(1, ClientMessage::where('intervention_id', $intervention->id)->count());
    }

    public function test_atelier_intervention_is_not_reminded_for_a_home_rule(): void
    {
        $client = Client::create(['type' => 'particulier', 'nom' => 'Test', 'telephone_mobile' => '0600000000']);

        Automatisme::create([
            'libelle' => 'Rappel domicile', 'evenement' => 'rendez_vous', 'offset_minutes' => -15,
            'type_lieu' => 'domicile', 'canal' => 'sms', 'modele' => 'Rappel', 'actif' => true,
        ]);

        Intervention::create(['client_id' => $client->id, 'type_lieu' => 'atelier', 'rdv_debut' => now()->addMinutes(10)]);

        $this->artisan('managy:run-automatismes')->assertSuccessful();
        $this->assertSame(0, ClientMessage::count());
    }
}
