<?php

namespace App\Livewire;

use App\Models\Commande;
use App\Support\Permissions;
use App\Support\Reception;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * "Commandes en cours" list: every supplier order still awaited, across all open
 * interventions. Whoever opens the parcel can mark the order received here
 * without opening the intervention — this unblocks it and notifies the
 * technician(s) in charge.
 */
class PendingCommandes extends Component
{
    use WithPagination;

    public string $q = '';

    public function mount(): void
    {
        Gate::authorize(Permissions::COMMANDES_RECEPTION);
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function receive(int $id): void
    {
        Gate::authorize(Permissions::COMMANDES_RECEPTION);

        $commande = Commande::where('recue', false)
            ->whereHas('intervention', fn ($i) => $i->whereNull('closed_at'))
            ->findOrFail($id);

        Reception::receiveCommande($commande);

        session()->flash('success', 'Commande réceptionnée. Le technicien en charge a été notifié.');
    }

    public function render()
    {
        $term = trim($this->q);

        $commandes = Commande::query()
            ->where('recue', false)
            ->whereHas('intervention', fn ($i) => $i->whereNull('closed_at'))
            ->when($term !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('fournisseur', 'like', "%{$term}%")
                ->orWhere('numero_commande', 'like', "%{$term}%")
                ->orWhere('suivi_colis', 'like', "%{$term}%")
                ->orWhereHas('intervention', fn ($i) => $i->where('reference', 'like', "%{$term}%")
                    ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', "%{$term}%")))))
            ->with(['intervention.client', 'intervention.techniciens'])
            ->latest('created_at')
            ->paginate(25);

        return view('livewire.pending-commandes', compact('commandes'));
    }
}
