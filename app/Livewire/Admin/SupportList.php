<?php

namespace App\Livewire\Admin;

use App\Models\SupportTicket;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Cross-société supervision list of every assistance ticket, for the platform
 * super-admin. Their tenancy is null, so the tickets are not scoped; each row
 * surfaces the requester and the société it belongs to.
 */
class SupportList extends Component
{
    use WithPagination;

    #[Url]
    public string $statut = '';

    #[Url]
    public string $q = '';

    public function updating($name): void
    {
        if (in_array($name, ['statut', 'q'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $tickets = SupportTicket::query()->withoutGlobalScope('society')
            ->with(['user', 'society'])
            ->withCount('messages')
            ->when($this->statut !== '', fn ($query) => $query->where('statut', $this->statut))
            ->when($this->q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('sujet', 'like', '%'.$this->q.'%')
                ->orWhereHas('society', fn ($s) => $s->where('name', 'like', '%'.$this->q.'%'))))
            ->orderByDesc('last_reply_at')
            ->orderByDesc('created_at')
            ->paginate(30);

        $counts = SupportTicket::query()->withoutGlobalScope('society')
            ->selectRaw('statut, count(*) as c')->groupBy('statut')->pluck('c', 'statut');

        return view('livewire.admin.support-list', compact('tickets', 'counts'));
    }
}
