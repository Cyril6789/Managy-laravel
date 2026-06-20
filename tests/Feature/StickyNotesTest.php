<?php

namespace Tests\Feature;

use App\Livewire\StickyNotes;
use App\Models\StickyNote;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StickyNotesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_add_edit_and_delete_without_reload(): void
    {
        $user = User::where('pseudo', 'admin')->firstOrFail();
        $this->actingAs($user);

        $component = Livewire::test(StickyNotes::class)->call('add');

        $note = StickyNote::where('user_id', $user->id)->firstOrFail();

        // Editing the buffer persists on update (blur).
        $component->set("contenu.{$note->id}", 'Rappeler le client')->assertHasNoErrors();
        $this->assertSame('Rappeler le client', $note->fresh()->contenu);

        // Delete works through a Livewire action (touch-friendly, no hover).
        $component->call('delete', $note->id);
        $this->assertDatabaseMissing('sticky_notes', ['id' => $note->id]);
    }

    public function test_cannot_delete_another_users_note(): void
    {
        $owner = User::where('pseudo', 'admin')->firstOrFail();
        $note = StickyNote::create(['user_id' => $owner->id, 'contenu' => 'x', 'couleur' => '#fde68a', 'ordre' => 1]);

        $other = User::factory()->create();
        $this->actingAs($other);

        Livewire::test(StickyNotes::class)->call('delete', $note->id);

        $this->assertDatabaseHas('sticky_notes', ['id' => $note->id]);
    }
}
