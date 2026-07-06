<?php

namespace Tests\Feature;

use App\Livewire\Admin\SupportThread as AdminSupportThread;
use App\Livewire\Support\TicketThread;
use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\User;
use App\Support\Tenancy;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * A super-admin has no société, so from a tenant member's scope the author of a
 * "Support" reply used to resolve to null → "Utilisateur supprimé". Verify the
 * real super-admin name is shown instead.
 */
class SupportStaffAuthorTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_member_sees_super_admin_real_name_on_staff_reply(): void
    {
        $this->seed(DatabaseSeeder::class);

        $society = Society::where('slug', 'demo')->firstOrFail();
        $member = User::factory()->create(['society_id' => $society->id, 'is_admin' => false]);
        $superAdmin = User::where('is_super_admin', true)->firstOrFail();

        $ticket = app(Tenancy::class)->forSociety($society->id, fn () => SupportTicket::create([
            'user_id' => $member->id, 'sujet' => 'Question', 'description' => 'x',
            'type' => 'question', 'urgence' => 'normale', 'statut' => 'ouvert', 'last_reply_at' => now(),
        ]));

        // Super-admin answers as "Support".
        $this->actingAs($superAdmin);
        Livewire::test(AdminSupportThread::class, ['ticket' => $ticket])
            ->set('body', 'Voici notre réponse.')
            ->call('reply')
            ->assertHasNoErrors();

        // The tenant member opens the ticket: the reply carries the super-admin's
        // real name, not the "Utilisateur supprimé" fallback.
        $this->actingAs($member);
        Livewire::test(TicketThread::class, ['ticket' => $ticket->fresh()])
            ->assertSee($superAdmin->fullName())
            ->assertDontSee('Utilisateur supprimé');
    }
}
