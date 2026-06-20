<?php

namespace Tests\Feature;

use App\Livewire\ClientChat;
use App\Livewire\ClientPicker;
use App\Livewire\ContactManager;
use App\Livewire\ContactPicker;
use App\Livewire\Facturation;
use App\Livewire\InterventionPanel;
use App\Livewire\InterventionReport;
use App\Livewire\Tasks;
use App\Models\Task;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\Prestation;
use App\Models\Setting;
use App\Models\Statut;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('pseudo', 'admin')->firstOrFail());
    }

    public function test_client_picker_creates_client_from_typed_query(): void
    {
        Livewire::test(ClientPicker::class)
            ->set('query', 'Nouvelle Société SARL')
            ->call('openCreate')
            ->assertSet('form.nom', 'Nouvelle Société SARL') // typed text prefilled
            ->set('form.type', 'professionnel')
            ->call('save')
            ->assertSet('showModal', false)
            ->assertDispatched('client-selected');

        $this->assertDatabaseHas('clients', ['nom' => 'Nouvelle Société SARL']);
    }

    public function test_intervention_report_autosaves(): void
    {
        $intervention = Intervention::ouvertes()->first();

        Livewire::test(InterventionReport::class, ['intervention' => $intervention])
            ->set('diagnostic', 'Carte mère remplacée, tests OK')
            ->call('save');

        $this->assertSame('Carte mère remplacée, tests OK', $intervention->fresh()->diagnostic);
    }

    public function test_contact_manager_adds_a_contact_to_company(): void
    {
        $company = Client::create(['type' => 'professionnel', 'nom' => 'Globex']);

        Livewire::test(ContactManager::class, ['company' => $company])
            ->call('openCreate')
            ->set('form.nom', 'Martin')
            ->set('form.prenom', 'Paul')
            ->set('form.telephone_mobile', '0612345678')
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('clients', [
            'nom' => 'Martin',
            'prenom' => 'Paul',
            'type' => 'particulier',
        ]);
        $contact = Client::where('nom', 'Martin')->firstOrFail();
        $this->assertDatabaseHas('company_contact', [
            'company_id' => $company->id,
            'contact_id' => $contact->id,
        ]);
    }

    public function test_contact_picker_detects_company_and_creates_contact(): void
    {
        $company = Client::create(['type' => 'professionnel', 'nom' => 'Initech']);

        Livewire::test(ContactPicker::class)
            ->call('onClientSelected', $company->id)
            ->assertSet('isCompany', true)
            ->call('openCreate')
            ->set('form.nom', 'Lumbergh')
            ->call('save')
            ->assertSet('isCompany', true);

        $contact = Client::where('nom', 'Lumbergh')->firstOrFail();
        $this->assertSame('particulier', $contact->type);
        $this->assertDatabaseHas('company_contact', [
            'company_id' => $company->id,
            'contact_id' => $contact->id,
        ]);
    }

    public function test_panel_adds_prestation_and_changes_status_inline(): void
    {
        $intervention = Intervention::ouvertes()->first();
        $statut = Statut::where('id', '!=', $intervention->statut_id)->first();

        Livewire::test(InterventionPanel::class, ['intervention' => $intervention])
            ->set('presta.designation', 'Changement disque SSD')
            ->set('presta.duree', '1.5')
            ->call('addPrestation')
            ->set('statutId', $statut->id)
            ->call('changeStatut');

        $this->assertDatabaseHas('intervention_prestations', [
            'intervention_id' => $intervention->id,
            'designation' => 'Changement disque SSD',
        ]);
        $this->assertSame($statut->id, $intervention->fresh()->statut_id);
    }

    public function test_panel_accepts_comma_decimals_for_duration_and_price(): void
    {
        $intervention = Intervention::ouvertes()->first();

        Livewire::test(InterventionPanel::class, ['intervention' => $intervention])
            ->set('presta.designation', 'Diagnostic')
            ->set('presta.duree', '1,5')          // virgule décimale
            ->call('addPrestation')
            ->set('piece.designation', 'Câble HDMI')
            ->set('piece.prix', '12,50')          // virgule décimale
            ->set('piece.quantite', '2')
            ->call('addPiece')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('intervention_prestations', [
            'intervention_id' => $intervention->id,
            'designation' => 'Diagnostic',
            'duree' => 1.5,
        ]);
        $this->assertDatabaseHas('intervention_pieces', [
            'intervention_id' => $intervention->id,
            'designation' => 'Câble HDMI',
            'prix' => 12.5,
            'quantite' => 2,
        ]);
    }

    public function test_prestation_price_comes_from_catalogue(): void
    {
        $intervention = Intervention::ouvertes()->first();
        $catalogue = Prestation::create(['designation' => 'Forfait diag', 'duree_defaut' => 1, 'tarif' => 49]);

        Livewire::test(InterventionPanel::class, ['intervention' => $intervention])
            ->set('presta.prestation_id', $catalogue->id)
            ->call('selectPrestation')
            ->call('addPrestation');

        // The stored price is the catalogue tarif, not a technician-typed amount.
        $this->assertDatabaseHas('intervention_prestations', [
            'intervention_id' => $intervention->id,
            'designation' => 'Forfait diag',
            'tarif' => 49,
        ]);
    }

    public function test_panel_adds_and_removes_a_part(): void
    {
        $intervention = Intervention::ouvertes()->first();

        Livewire::test(InterventionPanel::class, ['intervention' => $intervention])
            ->set('piece.designation', 'Disque SSD 1 To')
            ->set('piece.prix', '80')
            ->set('piece.quantite', '2')
            ->call('addPiece');

        $this->assertDatabaseHas('intervention_pieces', [
            'intervention_id' => $intervention->id,
            'designation' => 'Disque SSD 1 To',
            'prix' => 80,
            'quantite' => 2,
        ]);
        $this->assertEqualsWithDelta(160.0, $intervention->fresh()->montantPieces(), 0.01);
    }

    public function test_pending_order_moves_status_to_waiting(): void
    {
        // Force the fallback (no configured statuses).
        Setting::query()->whereIn('key', ['statut_attente_id', 'statut_pret_id'])->delete();

        $intervention = Intervention::ouvertes()->first();

        Livewire::test(InterventionPanel::class, ['intervention' => $intervention])
            ->set('commande.fournisseur', 'LDLC')
            ->call('addCommande');

        $waiting = Statut::where('nom', 'En attente')->first();
        $this->assertSame($waiting->id, $intervention->fresh()->statut_id);
    }

    public function test_contact_picker_clears_contact_button_state(): void
    {
        $company = Client::create(['type' => 'professionnel', 'nom' => 'BigCorp']);
        $contact = $company->contacts()->create(['type' => 'particulier', 'nom' => 'Roy']);

        Livewire::test(ContactPicker::class)
            ->call('onClientSelected', $company->id)
            ->set('contactId', $contact->id)
            ->assertSeeHtml('Modifier')      // edit button visible once a contact is chosen
            ->set('contactId', null)
            ->assertDontSeeHtml('wire:click="openEdit"'); // and hidden again when cleared
    }

    public function test_facturation_marks_intervention_as_invoiced(): void
    {
        $intervention = Intervention::create([
            'client_id' => Client::first()->id,
            'closed_at' => now(),
            'facturee' => false,
        ]);

        Livewire::test(Facturation::class)
            ->assertSee($intervention->reference)
            ->call('facturer', $intervention->id);

        $this->assertTrue($intervention->fresh()->facturee);
    }

    public function test_staff_chat_and_public_chat_share_the_same_thread(): void
    {
        $intervention = Intervention::first();

        // Staff posts from the intervention panel chat
        Livewire::test(ClientChat::class, ['intervention' => $intervention])
            ->set('body', 'Bonjour, votre PC est prêt.')
            ->call('send');

        // The customer sees it on the public page (same intervention thread)
        $publicMessages = Livewire::test(ClientChat::class, ['token' => $intervention->public_token])
            ->get('intervention')->publicMessages;

        $this->assertCount(1, $publicMessages);
        $this->assertSame('staff', $publicMessages->first()->author);
    }

    public function test_public_chat_lets_customer_post(): void
    {
        $intervention = Intervention::first();

        Livewire::test(ClientChat::class, ['token' => $intervention->public_token])
            ->set('body', 'Bonjour, est-ce prêt ?')
            ->call('send')
            ->assertSet('body', '');

        $this->assertDatabaseHas('public_messages', [
            'intervention_id' => $intervention->id,
            'author' => 'client',
            'message' => 'Bonjour, est-ce prêt ?',
        ]);
    }

    public function test_tasks_component_creates_toggles_and_deletes(): void
    {
        $admin = User::where('pseudo', 'admin')->firstOrFail();

        $component = Livewire::test(Tasks::class)
            ->set('form.titre', 'Rappeler le client')
            ->set('form.heures_estimees', '1,5')   // virgule décimale acceptée
            ->call('create')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('tasks', [
            'titre' => 'Rappeler le client',
            'statut' => 'a_faire',
            'heures_estimees' => 1.5,
            'created_by' => $admin->id,
        ]);

        $task = Task::where('titre', 'Rappeler le client')->firstOrFail();

        $component->call('toggle', $task->id);
        $this->assertSame('terminee', $task->fresh()->statut);
        $this->assertNotNull($task->fresh()->completed_at);

        $component->call('toggle', $task->id);
        $this->assertSame('a_faire', $task->fresh()->statut);

        $component->call('delete', $task->id);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_tasks_component_requires_a_title(): void
    {
        Livewire::test(Tasks::class)
            ->set('form.titre', '')
            ->call('create')
            ->assertHasErrors(['form.titre' => 'required']);
    }
}
