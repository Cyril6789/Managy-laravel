<?php

namespace Tests\Feature;

use App\Livewire\InterventionSchedule;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduleEventAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_calendar_appointment_shows_in_technician_availability(): void
    {
        $tech = User::where('pseudo', 'admin')->firstOrFail();
        $day = now()->addDay()->setTime(14, 0);

        // Authenticate first so the created Event is stamped with the société.
        $this->actingAs($tech);

        Event::create([
            'user_id' => $tech->id,
            'titre' => 'Rendez-vous commercial',
            'debut' => $day,
            'fin' => $day->copy()->addHour(),
        ]);

        Livewire::test(InterventionSchedule::class, ['mode' => 'form'])
            ->set('rdv_debut', $day->copy()->setTime(15, 0)->format('Y-m-d\TH:i'))
            // The appointment now appears in the technician's agenda for the day,
            // so they are no longer shown as simply "Libre".
            ->assertSee('Rendez-vous commercial')
            ->assertSee('RDV');
    }
}
