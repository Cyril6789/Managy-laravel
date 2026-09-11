<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Services\InvoiceGenerator;
use App\Support\Permissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function store(Intervention $intervention, InvoiceGenerator $generator)
    {
        $this->authorize(Permissions::INTERVENTIONS_FACTURATION);
        abort_unless(request()->user()->society?->invoice_enabled, 404);

        $invoice = $generator->generate($intervention);

        if ($invoice->wasRecentlyCreated) {
            InterventionLog::create([
                'intervention_id' => $intervention->id,
                'user_id' => Auth::id(),
                'texte' => 'a généré la facture '.$invoice->number,
                'created_at' => now(),
            ]);
        }

        return redirect()->route('invoices.pdf', $invoice);
    }

    public function pdf(Invoice $invoice)
    {
        $this->authorize(Permissions::INTERVENTIONS_FACTURATION);
        abort_unless(request()->user()->society?->invoice_enabled, 404);

        abort_unless(Storage::disk('local')->exists($invoice->pdf_path), 404, 'Le fichier PDF archivé est introuvable.');

        $fileName = trim(preg_replace('/[^A-Za-z0-9._-]+/', '-', $invoice->number), '-').'.pdf';

        return response()->file(Storage::disk('local')->path($invoice->pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    public function paymentState(Invoice $invoice, InvoicePayment $payment)
    {
        $this->authorize(Permissions::INTERVENTIONS_FACTURATION);
        abort_unless(request()->user()->society?->invoice_enabled, 404);
        abort_unless($payment->invoice_id === $invoice->id && Storage::disk('local')->exists($payment->state_pdf_path), 404);

        return response()->file(Storage::disk('local')->path($payment->state_pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$invoice->number.'-etat-paiement.pdf"',
        ]);
    }
}
