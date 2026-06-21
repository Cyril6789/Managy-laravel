<?php

namespace App\Livewire;

use App\Models\Intervention;
use App\Support\Billing;
use App\Support\Deplacement;
use App\Support\Permissions;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Closing card (finalisation then restitution & signature). It lives next to the
 * intervention panel and recomputes its billing breakdown live: when the panel
 * adds or removes a prestation / part it dispatches `intervention-items-updated`,
 * which re-renders this component so the displayed total is always up to date —
 * no page reload. The form itself still posts to the regular routes.
 */
class InterventionCloture extends Component
{
    public Intervention $intervention;

    public function mount(Intervention $intervention): void
    {
        $this->intervention = $intervention;
    }

    /** Bump a key so the restitution Alpine form re-initialises with fresh amounts. */
    #[On('intervention-items-updated')]
    public function itemsUpdated(): void
    {
        $this->intervention->refresh();
    }

    public function render()
    {
        $i = $this->intervention->load(['prestations', 'pieces', 'client']);
        $breakdown = Billing::compute($i, $i->estDomicile() ? null : 0.0);

        $hasPack = (bool) $i->client?->maintenanceMovements()->exists();

        return view('livewire.intervention-cloture', [
            'i' => $i,
            'breakdown' => $breakdown,
            'deplMode' => Deplacement::mode(),
            'deplGratuit' => ($i->client?->deplacement_gratuit) || Deplacement::villeEstGratuite($i->client?->ville),
            'deplForfait' => Deplacement::forfait(),
            'deplPrixKm' => Deplacement::prixKm(),
            'peutRistourne' => Auth::user()->can(Permissions::INTERVENTIONS_RISTOURNE),
            'maintenanceHasPack' => $hasPack,
            'maintenanceSolde' => $hasPack ? max(0.0, $i->client->soldeMaintenance()) : 0.0,
            'totalHeures' => (float) $i->prestations->sum('duree'),
        ]);
    }
}
