<?php

namespace Tests\Feature;

use App\Livewire\ManualInvoice;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceTest extends TestCase
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

    public function test_super_admin_can_enable_invoice_module_for_a_society(): void
    {
        $society = User::where('pseudo', 'admin')->firstOrFail()->society;
        $society->update(['invoice_enabled' => false]);
        $this->actingAs(User::withoutGlobalScope('society')->where('is_super_admin', true)->firstOrFail());

        $this->post(route('admin.society.invoice.toggle', $society))->assertRedirect();

        $this->assertTrue($society->fresh()->invoice_enabled);
    }

    public function test_it_generates_numbered_immutable_pdf_invoice_from_intervention(): void
    {
        Setting::put('company_name', 'Dépannage Martin');
        Setting::put('company_address', '1 rue des Tests');
        Setting::put('company_siret', '123 456 789 00012');
        Setting::put('invoice_number_format', 'F-{YY}-{MM}-###');
        Setting::put('invoice_next_number', 42);
        Setting::put('invoice_terms', 'Conditions générales personnalisées.');
        Setting::put('invoice_payment_terms', 'Paiement sous 30 jours.');

        $client = Client::create([
            'type' => 'particulier',
            'nom' => 'Dupont',
            'prenom' => 'Alice',
            'adresse' => '12 rue du Client',
            'code_postal' => '75001',
            'ville' => 'Paris',
            'telephone_mobile' => '0612345678',
        ]);
        $intervention = Intervention::create([
            'client_id' => $client->id,
            'closed_at' => now(),
            'montant_prestations' => 120,
            'montant_pieces' => 40,
            'montant_deplacement' => 15,
            'montant_total' => 175,
            'facturee' => false,
        ]);
        $intervention->prestations()->create([
            'designation' => 'Dépannage informatique',
            'duree' => 2,
            'tarif' => 60,
        ]);
        $intervention->pieces()->create([
            'designation' => 'Disque SSD',
            'quantite' => 1,
            'prix' => 40,
        ]);

        $response = $this->post(route('invoices.store', $intervention));

        $invoice = Invoice::sole();
        $response->assertRedirect(route('invoices.pdf', $invoice));
        $this->assertSame('F-'.now()->format('y-m').'-042', $invoice->number);
        $this->assertSame('Dépannage Martin', $invoice->issuer['name']);
        $this->assertSame('Alice', explode(' ', $invoice->customer['name'])[1]);
        $this->assertSame('175.00', $invoice->total_ht);
        $this->assertSame('175.00', $invoice->total_ttc);
        $this->assertFalse($invoice->vat_enabled);
        $this->assertStringContainsString('293 B', $invoice->legal_notice);
        $this->assertSame('Conditions générales personnalisées.', $invoice->terms);
        $this->assertSame('Paiement sous 30 jours.', $invoice->payment_terms);
        $this->assertTrue($intervention->fresh()->facturee);
        $this->assertSame('43', Setting::get('invoice_next_number'));
        Storage::disk('local')->assertExists($invoice->pdf_path);
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($invoice->pdf_path));

        $client->update(['nom' => 'Nom modifié']);
        Setting::put('invoice_terms', 'Texte modifié après émission.');
        $this->post(route('invoices.store', $intervention));
        $this->assertSame(1, Invoice::count());
        $this->assertSame('Dupont Alice', $invoice->fresh()->customer['name']);
        $this->assertSame('Conditions générales personnalisées.', $invoice->fresh()->terms);
    }

    public function test_archived_pdf_is_viewable_by_authorized_user(): void
    {
        $intervention = Intervention::cloturees()->firstOrFail();
        $this->post(route('invoices.store', $intervention));
        $invoice = Invoice::sole();

        $this->get(route('invoices.pdf', $invoice))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_invoice_applies_configured_vat_and_removes_franchise_notice(): void
    {
        Setting::put('invoice_vat_enabled', true);
        Setting::put('invoice_vat_rate', 20);
        $intervention = Intervention::cloturees()->firstOrFail();
        $intervention->update(['montant_total' => 100, 'facturee' => false]);

        $this->post(route('invoices.store', $intervention));

        $invoice = Invoice::sole();
        $this->assertTrue($invoice->vat_enabled);
        $this->assertSame('20.00', $invoice->vat_rate);
        $this->assertSame('20.00', $invoice->vat_amount);
        $this->assertSame('120.00', $invoice->total_ttc);
        $this->assertSame('', $invoice->legal_notice);
    }

    public function test_manual_invoice_accepts_ttc_line_without_intervention(): void
    {
        Setting::put('invoice_vat_enabled', true);
        Setting::put('invoice_vat_rate', 20);
        $client = Client::firstOrFail();

        Livewire::test(ManualInvoice::class)
            ->call('open')
            ->set('clientId', $client->id)
            ->set('draft.description', 'Forfait assistance')
            ->set('draft.quantity', '2')
            ->set('draft.unit', 'u')
            ->set('draft.unit_price', '120')
            ->set('draft.price_mode', 'ttc')
            ->set('draft.vat_rate', '20')
            ->call('addLine')
            ->call('generate');

        $invoice = Invoice::sole();
        $this->assertNull($invoice->intervention_id);
        $this->assertSame($client->id, $invoice->client_id);
        $this->assertSame('200.00', $invoice->total_ht);
        $this->assertSame('40.00', $invoice->vat_amount);
        $this->assertSame('240.00', $invoice->total_ttc);
        $this->assertSame('100', (string) $invoice->lines[0]['unit_price_ht']);
    }
}
