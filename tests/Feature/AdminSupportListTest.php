<?php

namespace Tests\Feature;

use App\Livewire\Admin\SupportList;
use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\User;
use App\Support\Tenancy;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the super-admin assistance list: default "non clôturés" filter,
 * per-column filters / search / sort, and the full-row click-through.
 */
class AdminSupportListTest extends TestCase
{
    use RefreshDatabase;

    private Society $demo;

    private Society $autre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->demo = Society::where('slug', 'demo')->firstOrFail();
        $this->autre = Society::create(['name' => 'Autre SARL', 'is_active' => true]);

        $demoUser = User::where('pseudo', 'admin')->firstOrFail();
        $autreUser = User::factory()->create(['society_id' => $this->autre->id, 'is_admin' => true]);

        $this->makeTicket($this->demo, $demoUser, 'Alpha', 'bug', 'urgente', 'ouvert');
        $this->makeTicket($this->demo, $demoUser, 'Beta', 'question', 'basse', 'resolu');
        $this->makeTicket($this->autre, $autreUser, 'Gamma', 'suggestion', 'normale', 'ouvert');

        $this->actingAs(User::where('is_super_admin', true)->firstOrFail());
    }

    private function makeTicket(Society $society, User $user, string $sujet, string $type, string $urgence, string $statut): SupportTicket
    {
        return app(Tenancy::class)->forSociety($society->id, fn () => SupportTicket::create([
            'user_id' => $user->id, 'sujet' => $sujet, 'description' => 'x',
            'type' => $type, 'urgence' => $urgence, 'statut' => $statut,
            'closed_at' => in_array($statut, ['resolu', 'ferme'], true) ? now() : null,
            'last_reply_at' => now(),
        ]));
    }

    public function test_defaults_to_non_closed_and_has_no_traiter_link(): void
    {
        Livewire::test(SupportList::class)
            ->assertSet('statut', 'non_clotures')
            ->assertSet('sort', 'activite')
            ->assertSet('dir', 'desc')
            ->assertSee('Alpha')
            ->assertSee('Gamma')
            ->assertDontSee('Beta')      // résolu → masqué par défaut
            ->assertDontSee('Traiter');
    }

    public function test_all_status_reveals_closed_tickets(): void
    {
        Livewire::test(SupportList::class)
            ->set('statut', '')
            ->assertSee('Alpha')
            ->assertSee('Beta');
    }

    public function test_filter_by_type_and_society(): void
    {
        Livewire::test(SupportList::class)
            ->set('statut', '')
            ->set('type', 'question')
            ->assertSee('Beta')
            ->assertDontSee('Alpha')
            ->assertDontSee('Gamma');

        Livewire::test(SupportList::class)
            ->set('societyId', (string) $this->autre->id)
            ->assertSee('Gamma')
            ->assertDontSee('Alpha');
    }

    public function test_search_matches_society_name(): void
    {
        Livewire::test(SupportList::class)
            ->set('q', 'Autre')
            ->assertSee('Gamma')
            ->assertDontSee('Alpha');
    }

    public function test_sort_by_urgency_toggles_direction(): void
    {
        // basse (Beta) → urgente (Alpha) ascending; needs the closed ones visible.
        Livewire::test(SupportList::class)
            ->set('statut', '')
            ->call('sortBy', 'urgence')
            ->assertSet('dir', 'asc')
            ->assertSeeInOrder(['Beta', 'Alpha'])
            ->call('sortBy', 'urgence')   // re-click flips to desc
            ->assertSet('dir', 'desc')
            ->assertSeeInOrder(['Alpha', 'Beta']);
    }

    public function test_reset_filters_restores_defaults(): void
    {
        Livewire::test(SupportList::class)
            ->set('type', 'bug')
            ->set('q', 'Alpha')
            ->call('resetFilters')
            ->assertSet('statut', 'non_clotures')
            ->assertSet('type', '')
            ->assertSet('q', '');
    }
}
