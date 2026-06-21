<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\InterventionPhoto;
use App\Support\Permissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/**
 * Streams intervention photos straight from the public disk (independent of the
 * storage symlink, which may be missing in ephemeral environments — same
 * approach as the company logo). Two entry points: authenticated staff and the
 * token-protected customer page (where private photos are never served).
 */
class InterventionPhotoController extends Controller
{
    public function show(Intervention $intervention, InterventionPhoto $photo)
    {
        Gate::authorize(Permissions::INTERVENTIONS_VIEW);
        abort_unless($photo->intervention_id === $intervention->id, 404);

        return $this->stream($photo);
    }

    public function showPublic(string $token, InterventionPhoto $photo)
    {
        $intervention = Intervention::where('public_token', $token)->firstOrFail();
        abort_unless($photo->intervention_id === $intervention->id && ! $photo->prive, 404);

        return $this->stream($photo);
    }

    private function stream(InterventionPhoto $photo)
    {
        abort_unless(Storage::disk('public')->exists($photo->path), 404);

        return response()->file(Storage::disk('public')->path($photo->path), [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
