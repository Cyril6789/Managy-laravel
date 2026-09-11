<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Models\InvoiceDraft;
use App\Models\Prestation;
use App\Models\Setting;
use App\Services\InvoiceGenerator;
use App\Support\Permissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;

class ManualInvoice extends Component
{
    public bool $show = false;

    public ?int $clientId = null;

    public ?int $interventionId = null;

    public ?int $draftId = null;

    public bool $launcher = true;

    public array $draft = [];

    public array $lines = [];

    public ?string $pdfUrl = null;

    public ?int $generatedInvoiceId = null;

    public string $totalDiscountType = 'euro';

    public string $totalDiscountValue = '';

    public function mount(): void
    {
        $this->resetDraft();
    }

    public function open(): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        abort_unless(auth()->user()->society?->invoice_enabled, 404);
        $this->resetEditor();
        $this->show = true;
    }

    #[On('open-invoice-editor')]
    public function openForIntervention(int $interventionId, InvoiceGenerator $generator): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        abort_unless(auth()->user()->society?->invoice_enabled, 404);
        $intervention = Intervention::cloturees()->whereDoesntHave('invoice')->findOrFail($interventionId);

        if ($draft = InvoiceDraft::where('intervention_id', $intervention->id)->first()) {
            $this->loadDraft($draft);

            return;
        }

        $this->resetEditor();
        $this->interventionId = $intervention->id;
        $this->clientId = $intervention->client_id;
        $this->lines = $generator->draftLinesForIntervention($intervention);
        $this->show = true;
    }

    #[On('open-invoice-draft')]
    public function openDraft(int $draftId): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        $this->loadDraft(InvoiceDraft::findOrFail($draftId));
    }

    public function selectCatalogue(): void
    {
        if ($prestation = Prestation::find($this->draft['catalogue_id'] ?: null)) {
            $this->draft['description'] = $prestation->designation;
            $this->draft['quantity'] = (string) ((float) $prestation->duree_defaut ?: 1);
            $this->draft['unit'] = 'h';
            $this->draft['unit_price'] = (string) ((float) $prestation->tarif);
            $this->draft['price_mode'] = 'ht';
        }
    }

    public function addLine(): void
    {
        $this->validate([
            'draft.description' => ['required', 'string', 'max:255'],
            'draft.quantity' => ['required', 'numeric', 'min:0.01'],
            'draft.unit' => ['nullable', 'string', 'max:20'],
            'draft.unit_price' => ['required', 'numeric', 'min:0'],
            'draft.price_mode' => ['required', 'in:ht,ttc'],
            'draft.vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $rate = (float) $this->draft['vat_rate'];
        $entered = (float) $this->draft['unit_price'];
        $unitPriceHt = $this->draft['price_mode'] === 'ttc' && $rate > 0
            ? round($entered / (1 + $rate / 100), 2)
            : round($entered, 2);

        $this->lines[] = [
            'description' => $this->draft['description'],
            'quantity' => (float) $this->draft['quantity'],
            'unit' => $this->draft['unit'] ?: 'u',
            'unit_price_ht' => $unitPriceHt,
            'vat_rate' => $rate,
            'discount_type' => '',
            'discount_value' => 0,
        ];
        $this->resetDraft();
    }

    public function addTravel(): void
    {
        $this->draft['catalogue_id'] = '';
        $this->draft['description'] = 'Frais de déplacement';
        $this->draft['quantity'] = '1';
        $this->draft['unit'] = 'forfait';
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function moveLine(int $from, int $to): void
    {
        if ($from === $to || ! isset($this->lines[$from], $this->lines[$to])) {
            return;
        }

        $line = array_splice($this->lines, $from, 1)[0];
        array_splice($this->lines, $to, 0, [$line]);
    }

    public function generate(InvoiceGenerator $generator): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        $this->validateEditor();

        $invoice = $this->interventionId
            ? $generator->generateFromIntervention(Intervention::findOrFail($this->interventionId), $this->lines, $this->totalDiscountType, (float) ($this->totalDiscountValue ?: 0))
            : $generator->generateManual(Client::findOrFail($this->clientId), $this->lines, $this->totalDiscountType, (float) ($this->totalDiscountValue ?: 0));
        if ($this->interventionId) {
            InterventionLog::create([
                'intervention_id' => $this->interventionId,
                'user_id' => Auth::id(),
                'texte' => 'a généré la facture '.$invoice->number,
                'created_at' => now(),
            ]);
        }
        if ($this->draftId) {
            InvoiceDraft::whereKey($this->draftId)->delete();
        }
        $this->show = false;
        $this->pdfUrl = route('invoices.pdf', $invoice);
        $this->generatedInvoiceId = $invoice->id;
        $this->dispatch('invoice-created');
        $this->resetEditor();
    }

    private function validateEditor(): void
    {
        $this->validate([
            'clientId' => ['required', 'exists:clients,id'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'lines.*.unit' => ['nullable', 'string', 'max:20'],
            'lines.*.unit_price_ht' => ['required', 'numeric'],
            'lines.*.vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'lines.*.discount_type' => ['nullable', 'in:euro,pourcent'],
            'lines.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'totalDiscountType' => ['required', 'in:euro,pourcent'],
            'totalDiscountValue' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    public function saveDraft(): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        $this->validateEditor();

        $draft = InvoiceDraft::updateOrCreate(
            ['id' => $this->draftId],
            [
                'society_id' => auth()->user()->society_id,
                'intervention_id' => $this->interventionId,
                'client_id' => $this->clientId,
                'created_by' => Auth::id(),
                'lines' => array_values($this->lines),
                'total_discount_type' => $this->totalDiscountType,
                'total_discount_value' => (float) ($this->totalDiscountValue ?: 0),
            ],
        );
        $this->draftId = $draft->id;
        $this->show = false;
        $this->dispatch('invoice-draft-saved');
    }

    public function closePdf(): void
    {
        $this->pdfUrl = null;
        $this->generatedInvoiceId = null;
    }

    #[On('invoice-pdf-selected')]
    #[On('invoice-payment-recorded')]
    public function selectPdf(string $url): void
    {
        $this->pdfUrl = $url;
    }

    private function resetDraft(): void
    {
        $vatEnabled = filter_var(Setting::get('invoice_vat_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $this->draft = [
            'catalogue_id' => '', 'description' => '', 'quantity' => '1', 'unit' => 'u',
            'unit_price' => '', 'price_mode' => 'ht',
            'vat_rate' => $vatEnabled ? (string) Setting::get('invoice_vat_rate', 20) : '0',
        ];
    }

    private function resetEditor(): void
    {
        $this->clientId = null;
        $this->interventionId = null;
        $this->draftId = null;
        $this->lines = [];
        $this->totalDiscountType = 'euro';
        $this->totalDiscountValue = '';
        $this->resetDraft();
    }

    private function loadDraft(InvoiceDraft $draft): void
    {
        $this->resetEditor();
        $this->draftId = $draft->id;
        $this->interventionId = $draft->intervention_id;
        $this->clientId = $draft->client_id;
        $this->lines = $draft->lines;
        $this->totalDiscountType = $draft->total_discount_type;
        $this->totalDiscountValue = (string) $draft->total_discount_value;
        $this->show = true;
    }

    public function render()
    {
        return view('livewire.manual-invoice', [
            'clients' => Client::active()->orderBy('nom')->get(),
            'catalogue' => Prestation::orderBy('designation')->get(),
            'vatEnabled' => filter_var(Setting::get('invoice_vat_enabled', false), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
