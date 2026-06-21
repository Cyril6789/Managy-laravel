<?php

namespace Tests\Feature;

use App\Livewire\InterventionPhotos;
use App\Models\Intervention;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InterventionPhotosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('public');
    }

    private function intervention(): Intervention
    {
        return Intervention::ouvertes()->first();
    }

    public function test_staff_can_upload_a_photo_and_it_is_stored(): void
    {
        $this->actingAs(User::where('pseudo', 'admin')->firstOrFail());
        $intervention = $this->intervention();

        Livewire::test(InterventionPhotos::class, ['intervention' => $intervention])
            ->set('uploads', [UploadedFile::fake()->image('panne.jpg')])
            ->call('save')
            ->assertHasNoErrors();

        $photo = $intervention->photos()->firstOrFail();
        $this->assertFalse($photo->prive);
        Storage::disk('public')->assertExists($photo->path);
    }

    public function test_private_photo_is_hidden_from_the_public_gallery(): void
    {
        $this->actingAs(User::where('pseudo', 'admin')->firstOrFail());
        $intervention = $this->intervention();

        $intervention->photos()->create(['path' => 'intervention-photos/pub.jpg', 'prive' => false]);
        $intervention->photos()->create(['path' => 'intervention-photos/priv.jpg', 'prive' => true]);

        Livewire::test(InterventionPhotos::class, ['token' => $intervention->public_token])
            ->assertViewHas('photos', fn ($photos) => $photos->count() === 1 && $photos->every(fn ($p) => ! $p->prive));
    }

    public function test_public_mode_cannot_upload_or_delete(): void
    {
        $intervention = $this->intervention();
        $photo = $intervention->photos()->create(['path' => 'intervention-photos/x.jpg', 'prive' => false]);

        Livewire::test(InterventionPhotos::class, ['token' => $intervention->public_token])
            ->call('delete', $photo->id)
            ->assertForbidden();

        $this->assertDatabaseHas('intervention_photos', ['id' => $photo->id]);
    }

    public function test_deleting_a_photo_removes_the_file(): void
    {
        $this->actingAs(User::where('pseudo', 'admin')->firstOrFail());
        $intervention = $this->intervention();

        Livewire::test(InterventionPhotos::class, ['intervention' => $intervention])
            ->set('uploads', [UploadedFile::fake()->image('a.jpg')])
            ->call('save');

        $photo = $intervention->photos()->firstOrFail();
        Storage::disk('public')->assertExists($photo->path);

        Livewire::test(InterventionPhotos::class, ['intervention' => $intervention])
            ->call('delete', $photo->id);

        Storage::disk('public')->assertMissing($photo->path);
        $this->assertDatabaseMissing('intervention_photos', ['id' => $photo->id]);
    }

    public function test_public_photo_route_streams_only_non_private_photos(): void
    {
        $intervention = $this->intervention();
        $public = $intervention->photos()->create(['path' => 'intervention-photos/p.jpg', 'prive' => false]);
        $private = $intervention->photos()->create(['path' => 'intervention-photos/s.jpg', 'prive' => true]);
        Storage::disk('public')->put('intervention-photos/p.jpg', 'x');
        Storage::disk('public')->put('intervention-photos/s.jpg', 'x');

        $this->get(route('public.intervention.photo', [$intervention->public_token, $public]))->assertOk();
        $this->get(route('public.intervention.photo', [$intervention->public_token, $private]))->assertNotFound();
    }
}
