<?php

namespace App\Livewire\Support;

use App\Models\SupportTicket;
use App\Services\Notifier;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Assistance ticket list for a société, with an inline "new request" modal —
 * everything happens without a page reload. Any user of the société sees every
 * ticket of their tenant (the tenant scope on the model enforces the isolation).
 */
class TicketList extends Component
{
    use WithPagination;

    #[Url]
    public string $statut = '';

    public bool $showModal = false;

    public array $form = [
        'sujet' => '',
        'type' => 'question',
        'urgence' => 'normale',
        'description' => '',
    ];

    public function updatingStatut(): void
    {
        $this->resetPage();
    }

    public function create()
    {
        $data = $this->validate([
            'form.sujet' => ['required', 'string', 'max:255'],
            'form.type' => ['required', 'in:'.implode(',', array_keys(SupportTicket::TYPES))],
            'form.urgence' => ['required', 'in:'.implode(',', array_keys(SupportTicket::URGENCES))],
            'form.description' => ['required', 'string', 'max:5000'],
        ])['form'];

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'sujet' => $data['sujet'],
            'type' => $data['type'],
            'urgence' => $data['urgence'],
            'description' => $data['description'],
            'statut' => 'ouvert',
            'last_reply_at' => now(),
        ]);

        // Keep the société's gérant(s) in copy of the newly opened ticket.
        Notifier::ticketProgress($ticket, 'Nouvelle demande : '.$ticket->sujet);

        return $this->redirectRoute('support.show', $ticket, navigate: true);
    }

    public function render()
    {
        $tickets = SupportTicket::query()
            ->with('user')
            ->withCount('messages')
            ->when($this->statut !== '', fn ($q) => $q->where('statut', $this->statut))
            ->orderByDesc('last_reply_at')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.support.ticket-list', [
            'tickets' => $tickets,
            'openCount' => SupportTicket::query()->open()->count(),
        ]);
    }
}
