<?php

namespace App\Livewire;

use App\Models\Intervention;
use App\Models\Notification;
use App\Services\Notifier;
use App\Support\ChatPresence;
use App\Support\Permissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ClientChat extends Component
{
    /** Customer side is bound to the secure token; staff side to the intervention id. */
    #[Locked]
    public ?string $token = null;

    #[Locked]
    public ?int $interventionId = null;

    #[Locked]
    public string $author = 'client';

    public string $body = '';

    public function mount(?Intervention $intervention = null, ?string $token = null): void
    {
        if ($token) {
            $resolved = Intervention::where('public_token', $token)->firstOrFail();
            $this->token = $token;
            $this->interventionId = $resolved->id;
            $this->author = 'client';
        } else {
            abort_unless((bool) $intervention?->exists, 404);
            Gate::authorize(Permissions::INTERVENTIONS_VIEW);
            $this->interventionId = $intervention->id;
            $this->author = 'staff';
        }
    }

    public function getInterventionProperty(): Intervention
    {
        return $this->token
            ? Intervention::where('public_token', $this->token)->firstOrFail()
            : Intervention::findOrFail($this->interventionId);
    }

    public function send(): void
    {
        $data = $this->validate(['body' => ['required', 'string', 'max:2000']]);
        $intervention = $this->intervention;

        if ($this->author === 'staff') {
            Gate::authorize(Permissions::INTERVENTIONS_VIEW);
        }

        $intervention->publicMessages()->create([
            'author' => $this->author,
            'user_id' => $this->author === 'staff' ? Auth::id() : null,
            'message' => $data['body'],
            'created_at' => now(),
        ]);

        if ($this->author === 'client') {
            // Staff members currently watching this chat are not notified
            // (they already see the message live).
            Notifier::clientChatMessage($intervention, 'Nouveau message du client (suivi)');
        }

        $this->body = '';
        $this->dispatch('chat-sent');
    }

    public function render()
    {
        // The staff side refreshes every 10s (wire:poll): keep presence alive and
        // clear this intervention's chat notifications while the user is watching.
        if ($this->author === 'staff' && Auth::id()) {
            ChatPresence::mark($this->interventionId, Auth::id());

            // Clear any chat notifications for this intervention while watching.
            Notification::where('user_id', Auth::id())
                ->where('intervention_id', $this->interventionId)
                ->whereNull('read_at')
                ->where('message', 'like', '%message du client%')
                ->update(['read_at' => now()]);
        }

        return view('livewire.client-chat', [
            'messages' => $this->intervention->publicMessages()->with('user')->get(),
        ]);
    }
}
