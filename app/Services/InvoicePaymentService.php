<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoicePaymentService
{
    public function record(Invoice $invoice, float $amount, string $method, $paidAt, ?string $reference = null, ?string $note = null): InvoicePayment
    {
        return DB::transaction(function () use ($invoice, $amount, $method, $paidAt, $reference, $note) {
            Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $invoice->refresh();
            $balance = $invoice->balanceDue();
            $amount = round($amount, 2);
            if ($amount <= 0 || $amount > $balance + 0.001) {
                throw ValidationException::withMessages(['amount' => 'Le montant doit être positif et ne pas dépasser le solde de '.number_format($balance, 2, ',', ' ').' €.']);
            }

            $path = "invoices/{$invoice->society_id}/payment-states/{$invoice->id}-".Str::uuid().'.pdf';
            $payment = InvoicePayment::create([
                'society_id' => $invoice->society_id,
                'invoice_id' => $invoice->id,
                'recorded_by' => Auth::id(),
                'amount' => $amount,
                'paid_at' => $paidAt,
                'method' => $method,
                'reference' => $reference,
                'note' => $note,
                'state_pdf_path' => $path,
            ]);

            $invoice->unsetRelation('payments');
            Storage::disk('local')->put($path, app(InvoiceGenerator::class)->renderPdf($invoice, $invoice->payments()->get(), $invoice->paymentStatus()));

            return $payment;
        });
    }
}
