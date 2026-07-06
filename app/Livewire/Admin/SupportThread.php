<?php

namespace App\Livewire\Admin;

use App\Models\SupportTicket;
use App\Services\Notifier;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * A single assistance ticket as seen by the platform super-admin: the full
 * conversation, an inline staff reply, and status / urgency controls — all
 * without a page reload. Every action notifies the opener and the société's
 * gérant(s).
 */
class SupportThread extends Component
{
    public SupportTicket $ticket;

    public string $body = '';

    public string $statut = '';

    public string $urgence = '';

    public function mount(SupportTicket $ticket): void
    {
        // The super-admin's tenancy is null, so the model is not scoped here.
        $this->ticket = $ticket;
        $this->statut = $ticket->statut;
        $this->urgence = $ticket->urgence;
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
            'is_staff' => true,
        ]);

        // A staff reply usually asks for the user's input → waiting on them.
        $this->ticket->fill(['last_reply_at' => now()]);
        if (! $this->ticket->isClosed()) {
            $this->ticket->statut = 'en_attente';
        }
        $this->ticket->save();

        Notifier::ticketProgress($this->ticket, 'Le support a répondu à votre demande.');

        $this->reset('body');
        $this->ticket->refresh();
        $this->statut = $this->ticket->statut;
    }

    public function updateStatus(): void
    {
        $data = $this->validate([
            'statut' => ['required', 'in:'.implode(',', array_keys(SupportTicket::STATUTS))],
            'urgence' => ['required', 'in:'.implode(',', array_keys(SupportTicket::URGENCES))],
        ]);

        $this->ticket->fill([
            'statut' => $data['statut'],
            'urgence' => $data['urgence'],
            'last_reply_at' => now(),
            'closed_at' => in_array($data['statut'], ['resolu', 'ferme'], true)
                ? ($this->ticket->closed_at ?? now())
                : null,
        ])->save();

        Notifier::ticketProgress($this->ticket, 'Statut mis à jour : '.$this->ticket->statutLabel().'.');

        $this->ticket->refresh();
    }

    public function render()
    {
        return view('livewire.admin.support-thread', [
            'messages' => $this->ticket->messages()->with('author')->get(),
        ]);
    }
}
