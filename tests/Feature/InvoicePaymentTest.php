<?php

namespace Tests\Feature;

use App\Livewire\InvoicePayments;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use App\Services\InvoiceGenerator;
use App\Services\InvoicePaymentService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('pseudo', 'admin')->firstOrFail();
        $admin->society->update(['invoice_enabled' => true]);
        $this->actingAs($admin);
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_multiple_payments_update_status_and_create_distinct_pdf_states(): void
    {
        $invoice = $this->invoiceFor(100);
        $original = Storage::disk('local')->get($invoice->pdf_path);
        $service = app(InvoicePaymentService::class);

        $this->assertSame('pending', $invoice->paymentStatus());
        $first = $service->record($invoice, 40, 'cb', now()->subDay(), 'CB-001', 'Acompte');
        $this->assertSame('partial', $invoice->paymentStatus());
        $this->assertEqualsWithDelta(60, $invoice->balanceDue(), 0.001);
        Storage::disk('local')->assertExists($first->state_pdf_path);

        $stateHtml = app(InvoiceGenerator::class)->renderPdf($invoice, $invoice->payments()->get(), $invoice->paymentStatus());
        $this->assertStringStartsWith('%PDF-', $stateHtml);

        $second = $service->record($invoice, 60, 'virement', now(), 'VIR-002');
        $this->assertSame('paid', $invoice->paymentStatus());
        $this->assertEqualsWithDelta(0, $invoice->balanceDue(), 0.001);
        $this->assertNotSame($first->state_pdf_path, $second->state_pdf_path);
        Storage::disk('local')->assertExists($second->state_pdf_path);
        $this->assertSame($original, Storage::disk('local')->get($invoice->pdf_path));

        $this->get(route('invoices.payment-state', [$invoice, $second]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_payment_cannot_exceed_remaining_balance_and_is_immutable(): void
    {
        $invoice = $this->invoiceFor(50);

        try {
            app(InvoicePaymentService::class)->record($invoice, 51, 'especes', now());
            $this->fail('Le trop-perçu aurait dû être refusé.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('amount', $exception->errors());
        }
        $this->assertSame(0, InvoicePayment::count());

        $payment = app(InvoicePaymentService::class)->record($invoice, 20, 'cheque', now());
        $this->expectException(\LogicException::class);
        $payment->update(['amount' => 10]);
    }

    public function test_payment_panel_accepts_another_mode_and_shows_history(): void
    {
        $invoice = $this->invoiceFor(30);

        Livewire::test(InvoicePayments::class, ['invoice' => $invoice])
            ->assertSee('En attente de paiement')
            ->set('form.amount', '10')
            ->set('form.method', 'autre')
            ->set('form.reference', 'COMP-01')
            ->call('record')
            ->assertHasNoErrors()
            ->assertSee('Partiellement payée')
            ->assertSee('COMP-01');
    }

    private function invoiceFor(float $amount): Invoice
    {
        $intervention = Intervention::create([
            'client_id' => Client::firstOrFail()->id,
            'closed_at' => now(),
            'montant_total' => $amount,
        ]);
        $this->post(route('invoices.store', $intervention));

        return Invoice::sole();
    }
}
