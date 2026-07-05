<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\User;
use App\Support\Permissions;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tests\TestCase;

/**
 * Verifies that business models are auditable on create / update / delete, that
 * deletions are soft and undoable (with cascade), and that the trail is scoped
 * to the current société.
 */
class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('pseudo', 'admin')->firstOrFail());
    }

    private function logsFor(string $type, int $id)
    {
        return ActivityLog::where('subject_type', $type)->where('subject_id', $id);
    }

    public function test_create_update_delete_are_all_audited_with_details(): void
    {
        // Create
        $client = Client::create(['nom' => 'Audité', 'ville' => 'Lyon']);
        $this->assertTrue($this->logsFor(Client::class, $client->id)->where('action', 'created')->exists());

        // Update → the previous value is recorded (old → new)
        $client->update(['ville' => 'Marseille']);
        $updated = $this->logsFor(Client::class, $client->id)->where('action', 'updated')->latest('id')->first();
        $this->assertNotNull($updated);
        $this->assertSame('Marseille', $updated->changes['new']['ville']);
        $this->assertSame('Lyon', $updated->changes['old']['ville']);

        // Delete → soft delete + full snapshot of the record
        $client->delete();
        $this->assertSoftDeleted($client);
        $deleted = $this->logsFor(Client::class, $client->id)->where('action', 'deleted')->first();
        $this->assertNotNull($deleted);
        $this->assertSame('Marseille', $deleted->changes['attributes']['ville']);
    }

    public function test_deleting_an_intervention_cascades_and_can_be_undone(): void
    {
        $intervention = Intervention::first();
        $prestation = $intervention->prestations()->create(['designation' => 'Diagnostic', 'tarif' => 10]);
        $commande = $intervention->commandes()->create(['fournisseur' => 'ACME']);

        $intervention->delete();

        // The whole file is soft-deleted together...
        $this->assertSoftDeleted($intervention);
        $this->assertSoftDeleted($prestation);
        $this->assertSoftDeleted($commande);

        // ...and the children are listed in the delete entry for restoration.
        $deleteLog = $this->logsFor(Intervention::class, $intervention->id)->where('action', 'deleted')->first();
        $this->assertNotNull($deleteLog);
        $cascaded = collect($deleteLog->changes['cascaded'])->pluck('id');
        $this->assertTrue($cascaded->contains($prestation->id));
        $this->assertTrue($cascaded->contains($commande->id));

        // Undo from the journal (admin bypasses the permission gate).
        $this->post(route('logs.restore', $deleteLog))->assertRedirect();

        $this->assertFalse($intervention->fresh()->trashed());
        $this->assertFalse($prestation->fresh()->trashed());
        $this->assertFalse($commande->fresh()->trashed());
        $this->assertNotNull($deleteLog->fresh()->undone_at);
    }

    public function test_restore_requires_the_audit_permission(): void
    {
        $client = Client::create(['nom' => 'À restaurer']);
        $client->delete();
        $log = $this->logsFor(Client::class, $client->id)->where('action', 'deleted')->firstOrFail();

        // A technician without audit.restore cannot undo a deletion.
        $tech = User::where('pseudo', 'tech')->firstOrFail();
        $this->assertFalse($tech->hasPermission(Permissions::AUDIT_RESTORE));

        $this->actingAs($tech)
            ->post(route('logs.restore', $log))
            ->assertForbidden();

        $this->assertSoftDeleted($client);
    }

    public function test_journal_page_renders_entries_with_undo_for_a_permitted_user(): void
    {
        $client = Client::create(['nom' => 'Journal', 'ville' => 'Nice']);
        $client->update(['ville' => 'Cannes']);
        $client->delete();

        $this->get(route('logs.index'))
            ->assertOk()
            ->assertSee('Suppression')   // action badge
            ->assertSee('Modification')
            ->assertSee('Détails')       // expandable detail (diff / snapshot)
            ->assertSee('Annuler');      // undo button (admin holds the permission)
    }

    public function test_email_uniqueness_ignores_soft_deleted_users(): void
    {
        $rule = ['email' => [Rule::unique('users', 'email')->whereNull('deleted_at')]];

        // An active user's e-mail is still protected.
        $active = User::where('pseudo', 'admin')->firstOrFail();
        $this->assertTrue(Validator::make(['email' => $active->email], $rule)->fails());

        // Once soft-deleted, that address is free again.
        $tech = User::where('pseudo', 'tech')->firstOrFail();
        $email = $tech->email;
        $tech->delete();

        $this->assertSoftDeleted($tech);
        $this->assertTrue(Validator::make(['email' => $email], $rule)->passes());
    }
}
