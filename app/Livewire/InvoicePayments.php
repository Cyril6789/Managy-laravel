<?php

namespace App\Livewire;

use App\Models\Invoice;
use App\Services\InvoicePaymentService;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class InvoicePayments extends Component
{
    public Invoice $invoice;

    public array $form = [];

    public function mount(Invoice $invoice): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        $this->invoice = $invoice;
        $this->resetForm();
    }

    public function record(InvoicePaymentService $service): void
    {
        Gate::authorize(Permissions::INTERVENTIONS_FACTURATION);
        $data = $this->validate([
            'form.amount' => ['required', 'numeric', 'gt:0'],
            'form.paid_at' => ['required', 'date'],
            'form.method' => ['required', 'in:especes,cb,cheque,virement,autre'],
            'form.reference' => ['nullable', 'string', 'max:255'],
            'form.note' => ['nullable', 'string', 'max:1000'],
        ])['form'];

        $payment = $service->record($this->invoice, (float) $data['amount'], $data['method'], $data['paid_at'], $data['reference'] ?: null, $data['note'] ?: null);
        $this->invoice->refresh();
        $this->resetForm();
        $this->dispatch('invoice-payment-recorded', invoiceId: $this->invoice->id, url: route('invoices.payment-state', [$this->invoice, $payment]));
    }

    private function resetForm(): void
    {
        $this->form = [
            'amount' => number_format($this->invoice->balanceDue(), 2, '.', ''),
            'paid_at' => now()->format('Y-m-d\TH:i'),
            'method' => 'cb',
            'reference' => '',
            'note' => '',
        ];
    }

    public function render()
    {
        return view('livewire.invoice-payments', ['payments' => $this->invoice->payments()->with('recorder')->get()]);
    }
}
