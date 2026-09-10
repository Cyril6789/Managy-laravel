<?php

namespace App\Livewire;

use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Models\Invoice;
use App\Services\InvoiceGenerator;
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

    public ?string $pdfUrl = null;

    public function updating($name): void
    {
        if (in_array($name, ['filtre', 'q'], true)) {
            $this->resetPage();
        }
    }

    public function generate(int $id, InvoiceGenerator $generator): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        $intervention = Intervention::cloturees()->findOrFail($id);
        $invoice = $generator->generate($intervention);

        if ($invoice->wasRecentlyCreated) {
            InterventionLog::create([
                'intervention_id' => $intervention->id,
                'user_id' => Auth::id(),
                'texte' => 'a généré la facture '.$invoice->number,
                'created_at' => now(),
            ]);
        }

        $this->pdfUrl = route('invoices.pdf', $invoice);
    }

    public function openPdf(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        $this->pdfUrl = route('invoices.pdf', Invoice::findOrFail($id));
    }

    public function closePdf(): void
    {
        $this->pdfUrl = null;
    }

    public function render()
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);

        $term = '%'.trim($this->q).'%';
        $interventions = null;
        $invoices = null;

        if ($this->filtre === 'a_facturer') {
            $interventions = Intervention::cloturees()
                ->whereDoesntHave('invoice')
                ->when($this->q !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('reference', 'like', $term)
                    ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', $term)->orWhere('prenom', 'like', $term))))
                ->with(['client', 'prestations'])
                ->latest('closed_at')
                ->paginate(20);
        } else {
            $invoices = Invoice::query()
                ->when($this->q !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('number', 'like', $term)
                    ->orWhereHas('intervention', fn ($i) => $i->where('reference', 'like', $term)
                        ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', $term)->orWhere('prenom', 'like', $term)))))
                ->with(['intervention.client'])
                ->latest('issued_at')->latest('id')
                ->paginate(20);
        }

        return view('livewire.facturation', [
            'interventions' => $interventions,
            'invoices' => $invoices,
            'totalAFacturer' => Intervention::cloturees()->whereDoesntHave('invoice')->count(),
        ]);
    }
}
