<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Prestation;
use App\Models\Setting;
use App\Services\InvoiceGenerator;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ManualInvoice extends Component
{
    public bool $show = false;

    public ?int $clientId = null;

    public array $draft = [];

    public array $lines = [];

    public ?string $pdfUrl = null;

    public function mount(): void
    {
        $this->resetDraft();
    }

    public function open(): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        abort_unless(auth()->user()->society?->invoice_enabled, 404);
        $this->show = true;
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

    public function generate(InvoiceGenerator $generator): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        $this->validate(['clientId' => ['required', 'exists:clients,id'], 'lines' => ['required', 'array', 'min:1']]);

        $invoice = $generator->generateManual(Client::findOrFail($this->clientId), $this->lines);
        $this->show = false;
        $this->pdfUrl = route('invoices.pdf', $invoice);
        $this->dispatch('invoice-created');
        $this->clientId = null;
        $this->lines = [];
        $this->resetDraft();
    }

    public function closePdf(): void
    {
        $this->pdfUrl = null;
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

    public function render()
    {
        return view('livewire.manual-invoice', [
            'clients' => Client::active()->orderBy('nom')->get(),
            'catalogue' => Prestation::orderBy('designation')->get(),
            'vatEnabled' => filter_var(Setting::get('invoice_vat_enabled', false), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
