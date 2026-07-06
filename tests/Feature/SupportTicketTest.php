<?php

namespace Tests\Feature;

use App\Livewire\Admin\SupportThread as AdminSupportThread;
use App\Livewire\Support\TicketList;
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
 * Covers the "Assistance" ticket feature: opening a ticket, tenant segregation,
 * the two-way conversation, status handling and the notifications that keep the
 * opener AND the société's gérant in copy of every advancement.
 */
class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    private Society $society;

    private User $gerant;

    private User $member;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->society = Society::where('slug', 'demo')->firstOrFail();
        $this->gerant = User::where('pseudo', 'admin')->firstOrFail();
        $this->superAdmin = User::where('is_super_admin', true)->firstOrFail();

        // A regular (non-gérant) member of the same société, who opens tickets.
        $this->member = User::factory()->create([
            'society_id' => $this->society->id,
            'is_admin' => false,
            'is_active' => true,
        ]);
    }

    private function openTicket(User $as, array $attrs = []): SupportTicket
    {
        $this->actingAs($as);

        Livewire::test(TicketList::class)
            ->set('form.sujet', $attrs['sujet'] ?? 'Un bug à corriger')
            ->set('form.type', $attrs['type'] ?? 'bug')
            ->set('form.urgence', $attrs['urgence'] ?? 'haute')
            ->set('form.description', $attrs['description'] ?? 'Ceci ne fonctionne pas.')
            ->call('create')
            ->assertHasNoErrors();

        return SupportTicket::withoutGlobalScope('society')->latest('id')->firstOrFail();
    }

    public function test_member_opens_ticket_and_gerant_is_kept_in_copy(): void
    {
        $ticket = $this->openTicket($this->member);

        $this->assertSame($this->member->id, $ticket->user_id);
        $this->assertSame($this->society->id, $ticket->society_id);
        $this->assertSame('ouvert', $ticket->statut);

        // The gérant is notified (kept in copy), inside the société's scope.
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->gerant->id,
            'society_id' => $this->society->id,
        ]);

        // The opener (the actor) is not notified about their own action.
        $this->assertDatabaseMissing('app_notifications', ['user_id' => $this->member->id]);
    }

    public function test_tickets_are_segregated_per_tenant(): void
    {
        // A ticket in another société.
        $otherSociety = Society::create(['name' => 'Autre SARL', 'is_active' => true]);
        $otherUser = User::factory()->create(['society_id' => $otherSociety->id, 'is_admin' => true]);
        $foreign = app(Tenancy::class)->forSociety($otherSociety->id, fn () => SupportTicket::create([
            'user_id' => $otherUser->id, 'sujet' => 'Chez les autres', 'description' => 'x',
            'type' => 'question', 'urgence' => 'normale', 'statut' => 'ouvert', 'last_reply_at' => now(),
        ]));

        $mine = $this->openTicket($this->member);

        // As a demo-société member, the foreign ticket is invisible…
        $this->actingAs($this->member);
        $this->assertNull(SupportTicket::find($foreign->id));
        $this->assertNotNull(SupportTicket::find($mine->id));

        // …and reaching its page is a 404 (tenant scope on route binding).
        $this->get(route('support.show', $foreign))->assertNotFound();
    }

    public function test_super_admin_reply_notifies_opener_and_gerant_and_flags_waiting(): void
    {
        $ticket = $this->openTicket($this->member);

        $this->actingAs($this->superAdmin);
        Livewire::test(AdminSupportThread::class, ['ticket' => $ticket])
            ->set('body', 'Bonjour, pouvez-vous préciser ?')
            ->call('reply')
            ->assertHasNoErrors();

        $ticket->refresh();
        $this->assertSame('en_attente', $ticket->statut);
        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'is_staff' => true,
            'society_id' => $this->society->id,
        ]);

        // Both the opener and the gérant get an in-copy notification, scoped to
        // the société so they actually see it in their tenant.
        foreach ([$this->member->id, $this->gerant->id] as $recipient) {
            $this->assertDatabaseHas('app_notifications', [
                'user_id' => $recipient,
                'society_id' => $this->society->id,
            ]);
        }
    }

    public function test_super_admin_status_change_notifies_and_closes(): void
    {
        $ticket = $this->openTicket($this->member);

        $this->actingAs($this->superAdmin);
        Livewire::test(AdminSupportThread::class, ['ticket' => $ticket])
            ->set('statut', 'resolu')
            ->set('urgence', 'basse')
            ->call('updateStatus')
            ->assertHasNoErrors();

        $ticket->refresh();
        $this->assertSame('resolu', $ticket->statut);
        $this->assertNotNull($ticket->closed_at);

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->member->id,
            'society_id' => $this->society->id,
        ]);
    }

    public function test_member_can_reply_on_their_ticket_without_reload(): void
    {
        $ticket = $this->openTicket($this->member);

        $this->actingAs($this->member);
        Livewire::test(TicketThread::class, ['ticket' => $ticket])
            ->set('body', 'Voici un complément d’information.')
            ->call('reply')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $this->member->id,
            'is_staff' => false,
        ]);
    }
}
