<?php

namespace App\Livewire;

use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Models\Invoice;
use App\Models\InvoiceDraft;
use App\Services\InvoiceGenerator;
use App\Support\Permissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Facturation extends Component
{
    use WithPagination;

    public string $filtre = 'a_facturer'; // a_facturer | facturees

    public string $q = '';

    public ?string $pdfUrl = null;

    public ?int $selectedInvoiceId = null;

    public bool $invoiceEnabled = false;

    public function mount(): void
    {
        $this->invoiceEnabled = (bool) Auth::user()->society?->invoice_enabled;
    }

    public function updating($name): void
    {
        if (in_array($name, ['filtre', 'q'], true)) {
            $this->resetPage();
        }
    }

    public function generate(int $id, InvoiceGenerator $generator): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        abort_unless($this->invoiceEnabled, 404);
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
        $this->selectedInvoiceId = $invoice->id;
    }

    public function openPdf(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        abort_unless($this->invoiceEnabled, 404);
        $invoice = Invoice::findOrFail($id);
        $this->selectedInvoiceId = $invoice->id;
        $this->pdfUrl = route('invoices.pdf', $invoice);
    }

    public function closePdf(): void
    {
        $this->pdfUrl = null;
        $this->selectedInvoiceId = null;
    }

    #[On('invoice-pdf-selected')]
    #[On('invoice-payment-recorded')]
    public function selectPdf(string $url, ?int $invoiceId = null): void
    {
        $this->pdfUrl = $url;
        $this->selectedInvoiceId = $invoiceId ?? $this->selectedInvoiceId;
    }

    #[On('invoice-created')]
    public function invoiceCreated(): void
    {
        $this->filtre = 'facturees';
        $this->resetPage();
    }

    #[On('invoice-draft-saved')]
    public function draftSaved(): void
    {
        $this->filtre = 'brouillons';
        $this->resetPage();
    }

    public function ignore(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        abort_unless($this->invoiceEnabled, 404);
        $intervention = Intervention::cloturees()->whereDoesntHave('invoice')->findOrFail($id);
        $intervention->update(['invoice_ignored_at' => now(), 'invoice_ignored_by' => Auth::id()]);
        $this->logLegacy($intervention, 'a ignoré l’intervention de la facturation');
    }

    public function restoreIgnored(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        abort_unless($this->invoiceEnabled, 404);
        $intervention = Intervention::cloturees()->whereNotNull('invoice_ignored_at')->findOrFail($id);
        $intervention->update(['invoice_ignored_at' => null, 'invoice_ignored_by' => null]);
        $this->logLegacy($intervention, 'a réintégré l’intervention à la facturation');
    }

    public function markInvoiced(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        abort_if($this->invoiceEnabled, 404);
        $intervention = Intervention::cloturees()->findOrFail($id);
        $intervention->update(['facturee' => true]);
        $this->logLegacy($intervention, 'a marqué comme facturée');
    }

    public function unmarkInvoiced(int $id): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        abort_if($this->invoiceEnabled, 404);
        $intervention = Intervention::cloturees()->findOrFail($id);
        $intervention->update(['facturee' => false]);
        $this->logLegacy($intervention, 'a retiré la facturation');
    }

    private function logLegacy(Intervention $intervention, string $text): void
    {
        InterventionLog::create([
            'intervention_id' => $intervention->id,
            'user_id' => Auth::id(),
            'texte' => $text,
            'created_at' => now(),
        ]);
    }

    public function render()
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);

        $term = '%'.trim($this->q).'%';
        $interventions = null;
        $invoices = null;
        $drafts = null;

        if (! $this->invoiceEnabled) {
            $interventions = Intervention::cloturees()
                ->where('facturee', $this->filtre === 'facturees')
                ->when($this->q !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('reference', 'like', $term)
                    ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', $term)->orWhere('prenom', 'like', $term))))
                ->with(['client', 'prestations'])
                ->latest('closed_at')
                ->paginate(20);
        } elseif ($this->filtre === 'a_facturer') {
            $interventions = Intervention::cloturees()
                ->whereNull('invoice_ignored_at')
                ->whereDoesntHave('invoice')
                ->when($this->q !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('reference', 'like', $term)
                    ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', $term)->orWhere('prenom', 'like', $term))))
                ->with(['client', 'prestations'])
                ->latest('closed_at')
                ->paginate(20);
        } elseif ($this->filtre === 'facturees') {
            $invoices = Invoice::query()
                ->when($this->q !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('number', 'like', $term)
                    ->orWhereHas('intervention', fn ($i) => $i->where('reference', 'like', $term)
                        ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', $term)->orWhere('prenom', 'like', $term)))
                    ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', $term)->orWhere('prenom', 'like', $term))))
                ->with(['intervention.client', 'client', 'payments'])
                ->latest('issued_at')->latest('id')
                ->paginate(20);
        } elseif ($this->filtre === 'ignorees') {
            $interventions = Intervention::cloturees()
                ->whereNotNull('invoice_ignored_at')
                ->when($this->q !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('reference', 'like', $term)
                    ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', $term)->orWhere('prenom', 'like', $term))))
                ->with(['client', 'invoiceIgnoredBy'])
                ->latest('invoice_ignored_at')
                ->paginate(20);
        } else {
            $drafts = InvoiceDraft::query()
                ->when($this->q !== '', fn ($query) => $query->whereHas('client', fn ($client) => $client->where('nom', 'like', $term)->orWhere('prenom', 'like', $term)))
                ->with(['client', 'intervention'])
                ->latest('updated_at')
                ->paginate(20);
        }

        return view('livewire.facturation', [
            'interventions' => $interventions,
            'invoices' => $invoices,
            'drafts' => $drafts,
            'totalAFacturer' => $this->invoiceEnabled
                ? Intervention::cloturees()->whereNull('invoice_ignored_at')->whereDoesntHave('invoice')->count()
                : Intervention::cloturees()->where('facturee', false)->count(),
        ]);
    }
}
