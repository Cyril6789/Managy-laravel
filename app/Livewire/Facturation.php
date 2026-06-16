<?php

namespace App\Livewire;

use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Support\Permissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class Facturation extends Component
{
    use WithPagination;

    public string $filtre = 'a_facturer'; // a_facturer | facturees

    public string $q = '';

    public function updating($name): void
    {
        if (in_array($name, ['filtre', 'q'], true)) {
            $this->resetPage();
        }
    }

    public function facturer(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        $intervention = Intervention::cloturees()->findOrFail($id);
        $intervention->update(['facturee' => true]);
        $this->log($intervention, 'a marqué comme facturée');
    }

    public function annuler(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        $intervention = Intervention::cloturees()->findOrFail($id);
        $intervention->update(['facturee' => false]);
        $this->log($intervention, 'a retiré la facturation');
    }

    public function facturerTout(): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);

        Intervention::cloturees()->where('facturee', false)->get()
            ->each(function (Intervention $i) {
                $i->update(['facturee' => true]);
                $this->log($i, 'a marqué comme facturée');
            });
    }

    private function log(Intervention $intervention, string $texte): void
    {
        InterventionLog::create([
            'intervention_id' => $intervention->id,
            'user_id' => Auth::id(),
            'texte' => $texte,
            'created_at' => now(),
        ]);
    }

    public function render()
    {
        $interventions = Intervention::cloturees()
            ->where('facturee', $this->filtre === 'facturees')
            ->when($this->q !== '', function ($query) {
                $term = '%'.trim($this->q).'%';
                $query->where(fn ($w) => $w->where('reference', 'like', $term)
                    ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', $term)->orWhere('prenom', 'like', $term)));
            })
            ->with(['client', 'prestations'])
            ->latest('closed_at')
            ->paginate(20);

        return view('livewire.facturation', [
            'interventions' => $interventions,
            'totalAFacturer' => Intervention::cloturees()->where('facturee', false)->count(),
        ]);
    }
}
