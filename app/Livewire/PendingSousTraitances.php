<?php

namespace App\Livewire;

use App\Models\SousTraitance;
use App\Support\Permissions;
use App\Support\Reception;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * "Sous-traitance en cours" list: every subcontracting still awaiting its
 * return. Whoever receives the return can mark it here without opening the
 * intervention — unblocking it and notifying the technician(s) in charge.
 */
class PendingSousTraitances extends Component
{
    use WithPagination;

    public string $q = '';

    public function mount(): void
    {
        Gate::authorize(Permissions::SOUS_TRAITANCES_RECEPTION);
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function markReturned(int $id): void
    {
        Gate::authorize(Permissions::SOUS_TRAITANCES_RECEPTION);

        $sst = SousTraitance::where('retournee', false)
            ->whereHas('intervention', fn ($i) => $i->whereNull('closed_at'))
            ->findOrFail($id);

        Reception::returnSousTraitance($sst);

        session()->flash('success', 'Retour de sous-traitance enregistré. Le technicien en charge a été notifié.');
    }

    public function render()
    {
        $term = trim($this->q);

        $sousTraitances = SousTraitance::query()
            ->where('retournee', false)
            ->whereHas('intervention', fn ($i) => $i->whereNull('closed_at'))
            ->when($term !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('nom', 'like', "%{$term}%")
                ->orWhere('numero_commande', 'like', "%{$term}%")
                ->orWhere('suivi_retour', 'like', "%{$term}%")
                ->orWhereHas('intervention', fn ($i) => $i->where('reference', 'like', "%{$term}%")
                    ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', "%{$term}%")))))
            ->with(['intervention.client', 'intervention.techniciens'])
            ->latest('created_at')
            ->paginate(25);

        return view('livewire.pending-sous-traitances', compact('sousTraitances'));
    }
}
