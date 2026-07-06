<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Event;
use App\Models\Intervention;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** The demo société's gérant, used as the subscribing technician. */
    private function technician(): User
    {
        return User::where('pseudo', 'admin')->firstOrFail();
    }

    public function test_feed_lists_the_technician_assigned_interventions(): void
    {
        $tech = $this->technician();
        // Authenticate so records are stamped with the technician's société.
        $this->actingAs($tech);

        $client = Client::create([
            'type' => 'particulier',
            'nom' => 'Durand',
            'prenom' => 'Marie',
            'adresse' => '12 rue des Lilas',
            'code_postal' => '75011',
            'ville' => 'Paris',
            'telephone_mobile' => '0600000000',
        ]);

        $intervention = Intervention::create([
            'client_id' => $client->id,
            'rdv_debut' => now()->addDay()->setTime(9, 0),
            'rdv_fin' => now()->addDay()->setTime(10, 30),
            'panne' => 'Écran noir au démarrage',
            'urgente' => true,
        ]);
        $intervention->techniciens()->attach($tech->id);

        // Scheduled but assigned to nobody -> must not appear in this feed.
        $other = Intervention::create([
            'client_id' => $client->id,
            'rdv_debut' => now()->addDays(2)->setTime(14, 0),
            'panne' => 'Autre panne',
        ]);

        $token = $tech->calendarToken();

        $response = $this->get(route('calendar.subscribe', ['token' => $token]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');

        $body = $response->getContent();

        $this->assertStringContainsString('BEGIN:VCALENDAR', $body);
        $this->assertStringContainsString('SUMMARY:⚠ URGENT — Durand Marie', $body);
        $this->assertStringContainsString('LOCATION:12 rue des Lilas\, 75011 Paris', $body);
        // Reported fault ("panne constatée") lands in the notes. Long DESCRIPTION
        // lines are folded at 75 octets (RFC 5545), so assert on the label only.
        $this->assertStringContainsString('Panne constatée :', $body);
        $this->assertStringContainsString('Intervention n° '.$intervention->reference, $body);
        $this->assertStringContainsString('Fiche intervention', $body);
        $this->assertStringContainsString('UID:intervention-'.$intervention->id.'@managy', $body);

        // The intervention assigned to nobody stays out of this feed.
        $this->assertStringNotContainsString('intervention-'.$other->id.'@managy', $body);
    }

    public function test_feed_includes_the_technician_own_appointments(): void
    {
        $tech = $this->technician();
        $this->actingAs($tech);

        Event::create([
            'user_id' => $tech->id,
            'titre' => 'Rendez-vous commercial',
            'debut' => now()->addDay()->setTime(11, 0),
            'fin' => now()->addDay()->setTime(12, 0),
        ]);

        $response = $this->get(route('calendar.subscribe', ['token' => $tech->calendarToken()]));

        $response->assertOk();
        $this->assertStringContainsString('SUMMARY:Rendez-vous commercial', $response->getContent());
    }

    public function test_feed_is_scoped_to_the_owner_society(): void
    {
        // The public route runs without a session; the controller must still
        // scope the feed to the owner's société.
        $tech = $this->technician();
        $this->actingAs($tech);

        $client = Client::create(['type' => 'particulier', 'nom' => 'Bernard']);
        $mine = Intervention::create([
            'client_id' => $client->id,
            'rdv_debut' => now()->addDay()->setTime(9, 0),
        ]);
        $mine->techniciens()->attach($tech->id);

        $body = $this->get(route('calendar.subscribe', ['token' => $tech->calendarToken()]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('UID:intervention-'.$mine->id.'@managy', $body);
    }

    public function test_unknown_token_returns_404(): void
    {
        $this->get(route('calendar.subscribe', ['token' => 'nope']))->assertNotFound();
    }

    public function test_rotating_the_token_breaks_the_old_link(): void
    {
        $tech = $this->technician();
        $old = $tech->calendarToken();

        $this->actingAs($tech)
            ->post(route('calendar.subscribe.rotate'))
            ->assertRedirect();

        $this->get(route('calendar.subscribe', ['token' => $old]))->assertNotFound();
        $this->assertNotSame($old, $tech->fresh()->calendar_token);
    }
}
