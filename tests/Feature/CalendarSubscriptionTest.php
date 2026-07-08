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
            'type_lieu' => 'domicile',
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
        // A home visit points at the client's address.
        $this->assertStringContainsString('LOCATION:12 rue des Lilas\, 75011 Paris', $body);
        $this->assertStringContainsString('UID:intervention-'.$intervention->id.'@managy', $body);

        // DESCRIPTION lines are folded at 75 octets (RFC 5545); unfold before
        // asserting on the notes' free text.
        $notes = str_replace("\r\n ", '', $body);
        $this->assertStringContainsString('Panne constatée :', $notes);
        $this->assertStringContainsString('Intervention n°', $notes);
        $this->assertStringContainsString('Fiche intervention', $notes);

        // The reference is woven with WORD JOINER (U+2060) so iOS does not turn it
        // into a phone number: the raw contiguous reference must NOT appear...
        $this->assertStringNotContainsString('Intervention n° '.$intervention->reference, $notes);
        // ...but stripping the invisible joiner brings it back verbatim.
        $this->assertStringContainsString(
            $intervention->reference,
            str_replace("\u{2060}", '', $notes),
        );

        // The intervention assigned to nobody stays out of this feed.
        $this->assertStringNotContainsString('intervention-'.$other->id.'@managy', $body);
    }

    public function test_notes_include_past_interventions_and_maintenance_balance(): void
    {
        $tech = $this->technician();
        $this->actingAs($tech);

        $client = Client::create(['type' => 'particulier', 'nom' => 'Moreau']);

        // Two closed (past) jobs for this client, plus one still open (ignored).
        Intervention::create(['client_id' => $client->id, 'closed_at' => now()->subMonth()]);
        Intervention::create(['client_id' => $client->id, 'closed_at' => now()->subWeek()]);
        Intervention::create(['client_id' => $client->id]);

        // Maintenance pack: +5h credited, −1.5h consumed -> 3,5 h balance.
        $client->maintenanceMovements()->create(['mouvement' => 5]);
        $client->maintenanceMovements()->create(['mouvement' => -1.5]);

        $upcoming = Intervention::create([
            'client_id' => $client->id,
            'rdv_debut' => now()->addDay()->setTime(9, 0),
        ]);
        $upcoming->techniciens()->attach($tech->id);

        $body = $this->get(route('calendar.subscribe', ['token' => $tech->calendarToken()]))
            ->assertOk()
            ->getContent();
        // Unfold (RFC 5545) and unescape the reserved comma before asserting.
        $notes = str_replace(["\r\n ", '\\,'], ['', ','], $body);

        $this->assertStringContainsString('Interventions passées : 2', $notes);
        $this->assertStringContainsString('Solde pack maintenance : 3,5 h', $notes);
    }

    public function test_in_shop_intervention_shows_atelier_as_location(): void
    {
        $tech = $this->technician();
        $this->actingAs($tech);

        $client = Client::create([
            'type' => 'particulier',
            'nom' => 'Petit',
            'adresse' => '9 rue du Commerce',
            'ville' => 'Lyon',
        ]);

        $intervention = Intervention::create([
            'client_id' => $client->id,
            'type_lieu' => 'atelier',
            'rdv_debut' => now()->addDay()->setTime(9, 0),
        ]);
        $intervention->techniciens()->attach($tech->id);

        $body = $this->get(route('calendar.subscribe', ['token' => $tech->calendarToken()]))
            ->assertOk()
            ->getContent();

        // In-shop job: the location reads "Atelier", not the client's address.
        $this->assertStringContainsString('LOCATION:Atelier', $body);
        $this->assertStringNotContainsString('9 rue du Commerce', $body);
    }

    public function test_feed_excludes_closed_interventions(): void
    {
        // Only ongoing interventions belong in the feed; a terminated ("clôturée")
        // job must not keep showing up in the technician's subscribed calendar.
        $tech = $this->technician();
        $this->actingAs($tech);

        $client = Client::create(['type' => 'particulier', 'nom' => 'Lefevre']);

        $open = Intervention::create([
            'client_id' => $client->id,
            'rdv_debut' => now()->addDay()->setTime(9, 0),
        ]);
        $open->techniciens()->attach($tech->id);

        $closed = Intervention::create([
            'client_id' => $client->id,
            'rdv_debut' => now()->addDay()->setTime(14, 0),
            'closed_at' => now(),
        ]);
        $closed->techniciens()->attach($tech->id);

        $body = $this->get(route('calendar.subscribe', ['token' => $tech->calendarToken()]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('UID:intervention-'.$open->id.'@managy', $body);
        $this->assertStringNotContainsString('UID:intervention-'.$closed->id.'@managy', $body);
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
