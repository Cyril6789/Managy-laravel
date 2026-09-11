<?php

namespace Tests\Feature;

use App\Livewire\ManualInvoice;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\Invoice;
use App\Models\InvoiceDraft;
use App\Models\Prestation;
use App\Models\Setting;
use App\Models\User;
use App\Services\InvoiceGenerator;
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

    public function test_intervention_invoice_opens_as_editable_draft_with_discounts_and_ordering(): void
    {
        $client = Client::firstOrFail();
        $intervention = Intervention::create([
            'client_id' => $client->id,
            'closed_at' => now(),
            'montant_prestations' => 100,
            'montant_pieces' => 20,
            'montant_total' => 120,
        ]);
        $intervention->prestations()->create(['designation' => 'Diagnostic', 'duree' => 2, 'tarif' => 50]);
        $intervention->pieces()->create(['designation' => 'Câble', 'quantite' => 1, 'prix' => 20]);

        Livewire::test(ManualInvoice::class)
            ->call('openForIntervention', $intervention->id)
            ->assertSet('show', true)
            ->assertSet('clientId', $client->id)
            ->assertCount('lines', 2)
            ->set('lines.0.description', 'Diagnostic complet')
            ->set('lines.0.discount_type', 'pourcent')
            ->set('lines.0.discount_value', 10)
            ->call('moveLine', 0, 1)
            ->set('totalDiscountType', 'euro')
            ->set('totalDiscountValue', 10)
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('show', false);

        $invoice = Invoice::sole();
        $this->assertSame($intervention->id, $invoice->intervention_id);
        $this->assertSame('Câble', $invoice->lines[0]['description']);
        $this->assertSame('Diagnostic complet', $invoice->lines[1]['description']);
        $this->assertSame(10.0, (float) $invoice->lines[1]['discount_amount']);
        $this->assertSame('100.00', $invoice->total_ht);
        $this->assertTrue($intervention->fresh()->facturee);
    }

    public function test_emitted_invoice_and_original_pdf_are_immutable(): void
    {
        $intervention = Intervention::cloturees()->firstOrFail();
        $this->post(route('invoices.store', $intervention));
        $invoice = Invoice::sole();
        $originalPdf = Storage::disk('local')->get($invoice->pdf_path);

        try {
            $invoice->update(['number' => 'MODIFIED']);
            $this->fail('La modification aurait dû être refusée.');
        } catch (\LogicException $exception) {
            $this->assertSame('Une facture émise est immuable.', $exception->getMessage());
        }

        try {
            $invoice->delete();
            $this->fail('La suppression aurait dû être refusée.');
        } catch (\LogicException $exception) {
            $this->assertSame('Une facture émise ne peut pas être supprimée.', $exception->getMessage());
        }

        $this->assertSame($originalPdf, Storage::disk('local')->get($invoice->pdf_path));
        $this->assertSame(1, Invoice::count());
    }

    public function test_free_invoice_combines_catalogue_free_lines_and_both_discount_levels(): void
    {
        $client = Client::firstOrFail();
        $catalogue = Prestation::create(['designation' => 'Forfait catalogue', 'duree_defaut' => 1, 'tarif' => 100]);

        Livewire::test(ManualInvoice::class)
            ->call('open')
            ->set('clientId', $client->id)
            ->set('draft.catalogue_id', $catalogue->id)
            ->call('selectCatalogue')
            ->call('addLine')
            ->set('draft.description', 'Ligne libre')
            ->set('draft.quantity', 1)
            ->set('draft.unit_price', 50)
            ->call('addLine')
            ->set('lines.0.discount_type', 'euro')
            ->set('lines.0.discount_value', 5)
            ->set('totalDiscountType', 'pourcent')
            ->set('totalDiscountValue', 10)
            ->call('generate')
            ->assertHasNoErrors();

        $invoice = Invoice::sole();
        $this->assertNull($invoice->intervention_id);
        $this->assertSame('5.00', number_format($invoice->lines[0]['discount_amount'], 2, '.', ''));
        $this->assertSame('130.50', $invoice->total_ht);
        $this->assertSame('Remise globale (10 %)', $invoice->lines[2]['description']);
    }

    public function test_invoice_editor_does_not_close_when_clicking_its_backdrop(): void
    {
        Livewire::test(ManualInvoice::class)
            ->call('open')
            ->assertSeeHtml('wire:key="manual-invoice-modal"')
            ->assertDontSeeHtml('wire:click.self="$set(\'show\', false)"');
    }

    public function test_free_invoice_client_selector_is_enabled(): void
    {
        Livewire::test(ManualInvoice::class)
            ->call('open')
            ->assertSeeHtml('name="manual_invoice_client"')
            ->assertSeeHtml('x-ref="trigger"')
            ->assertDontSeeHtml('x-ref="trigger" disabled');
    }

    public function test_invoice_add_line_toolbar_does_not_clip_dropdowns_or_force_horizontal_scroll(): void
    {
        Livewire::test(ManualInvoice::class)
            ->call('open')
            ->assertSeeHtml('xl:grid-cols-[minmax(165px,1.25fr)')
            ->assertDontSeeHtml('min-w-[1380px]')
            ->assertDontSeeHtml('overflow-x-auto pb-1')
            ->assertSee('+ Ajouter');
    }

    public function test_invoice_draft_has_no_number_and_can_be_resumed_then_issued(): void
    {
        $client = Client::firstOrFail();
        $component = Livewire::test(ManualInvoice::class)
            ->call('open')
            ->set('clientId', $client->id)
            ->set('draft.description', 'Conseil')
            ->set('draft.unit_price', 80)
            ->call('addLine')
            ->call('saveDraft')
            ->assertHasNoErrors()
            ->assertSet('show', false);

        $draft = InvoiceDraft::sole();
        $this->assertDatabaseCount('invoices', 0);
        $this->assertArrayNotHasKey('number', $draft->getAttributes());

        $component->call('openDraft', $draft->id)
            ->assertSet('show', true)
            ->set('lines.0.description', 'Conseil personnalisé')
            ->call('saveDraft');

        $this->assertSame('Conseil personnalisé', InvoiceDraft::sole()->lines[0]['description']);
        $this->assertSame(1, InvoiceDraft::count());

        $component->call('openDraft', $draft->id)->call('generate')->assertHasNoErrors();
        $this->assertDatabaseCount('invoice_drafts', 0);
        $this->assertSame('Conseil personnalisé', Invoice::sole()->lines[0]['description']);
    }

    public function test_invoice_is_visible_and_opens_from_client_and_intervention_pages(): void
    {
        $intervention = Intervention::cloturees()->firstOrFail();
        $this->post(route('invoices.store', $intervention));
        $invoice = Invoice::sole();

        $this->get(route('clients.show', $intervention->client))
            ->assertOk()
            ->assertSee($invoice->number)
            ->assertSee('Ouvrir la facture');

        $this->get(route('interventions.show', $intervention))
            ->assertOk()
            ->assertSee('Ouvrir la facture '.$invoice->number);

        Livewire::test(ManualInvoice::class, ['launcher' => false])
            ->dispatch('open-invoice-viewer', invoiceId: $invoice->id)
            ->assertSet('pdfUrl', route('invoices.pdf', $invoice))
            ->assertSet('generatedInvoiceId', $invoice->id);
    }

    public function test_vat_exempt_pdf_only_displays_ht_columns_and_totals(): void
    {
        Setting::query()->whereIn('key', ['invoice_terms', 'invoice_payment_terms'])->delete();
        $intervention = Intervention::cloturees()->firstOrFail();
        $this->post(route('invoices.store', $intervention));
        $invoice = Invoice::sole();

        $html = view('invoices.pdf', [
            'invoice' => $invoice,
            'logoDataUri' => null,
            'payments' => null,
            'paymentStatus' => null,
        ])->render();

        $this->assertStringContainsString('P.U. HT', $html);
        $this->assertStringContainsString('Total HT', $html);
        $this->assertStringNotContainsString('<th class="num">TVA</th>', $html);
        $this->assertStringNotContainsString('Total TTC', $html);
        $this->assertSame(InvoiceGenerator::DEFAULT_TERMS, $invoice->terms);
        $this->assertSame(InvoiceGenerator::DEFAULT_PAYMENT_TERMS, $invoice->payment_terms);
        $this->assertStringContainsString('Aucun escompte accordé', $html);
        $this->assertStringContainsString('indemnité forfaitaire de 40 €', $html);
        $this->assertStringContainsString('article 293 B', $html);
    }

    public function test_vat_exempt_editor_hides_all_vat_and_ttc_controls(): void
    {
        Setting::put('invoice_vat_enabled', false);

        Livewire::test(ManualInvoice::class)
            ->call('open')
            ->assertDontSee('Prix saisi en')
            ->assertDontSee('TVA (%)')
            ->assertDontSee('Total TTC')
            ->assertSee('Prix unitaire')
            ->assertSee('Total HT');
    }
}
