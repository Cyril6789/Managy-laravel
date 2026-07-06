<?php

namespace App\Livewire\Support;

use App\Models\SupportTicket;
use App\Services\Notifier;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * A single assistance ticket for the société: the conversation plus an inline
 * reply box and close / reopen actions — all without a page reload. The public
 * SupportTicket property is re-hydrated through the tenant scope, so a user can
 * only ever act on a ticket of their own société.
 */
class TicketThread extends Component
{
    public SupportTicket $ticket;

    public string $body = '';

    public function mount(SupportTicket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function reply(): void
    {
        $data = $this->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $this->ticket->messages()->create([
            'society_id' => $this->ticket->society_id,
            'user_id' => Auth::id(),
            'body' => $data['body'],
            'is_staff' => false,
        ]);

        // A société reply moves a ticket that was waiting on them back in motion.
        $this->ticket->fill(['last_reply_at' => now()]);
        if ($this->ticket->statut === 'en_attente') {
            $this->ticket->statut = 'en_cours';
        }
        $this->ticket->save();

        Notifier::ticketProgress($this->ticket, Auth::user()->fullName().' a répondu.');

        $this->reset('body');
        $this->ticket->refresh();
    }

    public function close(): void
    {
        $this->authorizeManage();

        $this->ticket->update(['statut' => 'ferme', 'closed_at' => now(), 'last_reply_at' => now()]);
        Notifier::ticketProgress($this->ticket, 'La demande a été fermée.');
    }

    public function reopen(): void
    {
        $this->authorizeManage();

        $this->ticket->update(['statut' => 'ouvert', 'closed_at' => null, 'last_reply_at' => now()]);
        Notifier::ticketProgress($this->ticket, 'La demande a été ré-ouverte.');
    }

    /** Only the opener or a gérant of the société may close / reopen. */
    private function authorizeManage(): void
    {
        abort_unless($this->ticket->user_id === Auth::id() || Auth::user()->is_admin, 403);
    }

    public function render()
    {
        return view('livewire.support.ticket-thread', [
            'messages' => $this->ticket->messages()->with('author')->get(),
        ]);
    }
}
