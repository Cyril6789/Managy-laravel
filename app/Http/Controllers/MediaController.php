<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Streams the company logo directly (independent of the public storage
     * symlink, which may be missing in ephemeral environments).
     */
    public function logo()
    {
        $path = Setting::get('company_logo');
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return response()->file(Storage::disk('public')->path($path), [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
