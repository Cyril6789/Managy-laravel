<?php

namespace Tests\Feature;

use App\Livewire\ClientChat;
use App\Livewire\ClientPicker;
use App\Livewire\InterventionPanel;
use App\Livewire\InterventionReport;
use App\Models\Intervention;
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
}
