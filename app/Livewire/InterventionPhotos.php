<?php

namespace App\Livewire;

use App\Models\Intervention;
use App\Models\InterventionLog;
use App\Support\Permissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Photo gallery for an intervention. Like ClientChat it runs in two modes:
 *  - staff (an Intervention is passed): upload zone + privacy toggle + delete;
 *  - public (a secure token is passed): read-only gallery, private photos hidden.
 */
class InterventionPhotos extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $interventionId = null;

    #[Locked]
    public ?string $token = null;

    /** Public (customer) mode: read-only and never exposes private photos. */
    #[Locked]
    public bool $public = false;

    /** Files selected from the gallery / camera (multiple). */
    public array $uploads = [];

    /** When set, the photos being uploaded stay hidden from the customer page. */
    public bool $prive = false;

    public function mount(?Intervention $intervention = null, ?string $token = null): void
    {
        if ($token) {
            $resolved = Intervention::where('public_token', $token)->firstOrFail();
            $this->token = $token;
            $this->interventionId = $resolved->id;
            $this->public = true;

            return;
        }

        abort_unless((bool) $intervention?->exists, 404);
        Gate::authorize(Permissions::INTERVENTIONS_VIEW);
        $this->interventionId = $intervention->id;
    }

    public function getInterventionProperty(): Intervention
    {
        return Intervention::findOrFail($this->interventionId);
    }

    /** Upload starts as soon as files are picked (no separate "save" button). */
    public function updatedUploads(): void
    {
        $this->save();
    }

    public function save(): void
    {
        abort_if($this->public, 403);
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);

        $this->validate([
            'uploads' => ['array', 'max:20'],
            'uploads.*' => ['image', 'max:8192'],   // 8 Mo per photo
        ]);

        $count = 0;
        foreach ($this->uploads as $upload) {
            $path = $upload->store('intervention-photos', 'public');
            $this->intervention->photos()->create([
                'user_id' => Auth::id(),
                'path' => $path,
                'original_name' => $upload->getClientOriginalName(),
                'prive' => $this->prive,
            ]);
            $count++;
        }

        if ($count > 0) {
            $s = $count > 1 ? 's' : '';
            $this->log('a ajouté '.$count.' photo'.$s.($this->prive ? ' (privée'.$s.')' : ''));
        }

        $this->uploads = [];
        $this->dispatch('photos-updated');
    }

    public function togglePrivacy(int $photoId): void
    {
        abort_if($this->public, 403);
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);

        $photo = $this->intervention->photos()->findOrFail($photoId);
        $photo->update(['prive' => ! $photo->prive]);
        $this->log('a rendu une photo '.($photo->prive ? 'privée' : 'visible par le client'));
    }

    public function delete(int $photoId): void
    {
        abort_if($this->public, 403);
        Gate::authorize(Permissions::INTERVENTIONS_MANAGE);

        $photo = $this->intervention->photos()->findOrFail($photoId);
        Storage::disk('public')->delete($photo->path);
        $photo->delete();
        $this->log('a supprimé une photo');
    }

    private function log(string $texte): void
    {
        InterventionLog::create([
            'intervention_id' => $this->interventionId,
            'user_id' => Auth::id(),
            'texte' => $texte,
            'created_at' => now(),
        ]);
    }

    public function render()
    {
        $query = $this->intervention->photos();
        if ($this->public) {
            $query->where('prive', false);
        }

        return view('livewire.intervention-photos', [
            'photos' => $query->get(),
        ]);
    }
}
