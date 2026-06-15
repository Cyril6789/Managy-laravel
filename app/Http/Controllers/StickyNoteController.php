<?php

namespace App\Http\Controllers;

use App\Models\StickyNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StickyNoteController extends Controller
{
    public function store(Request $request)
    {
        StickyNote::create([
            'user_id' => Auth::id(),
            'contenu' => $request->input('contenu', ''),
            'couleur' => $request->input('couleur', '#fde68a'),
            'ordre' => (int) StickyNote::where('user_id', Auth::id())->max('ordre') + 1,
        ]);

        return back();
    }

    public function update(Request $request, StickyNote $stickyNote)
    {
        abort_unless($stickyNote->user_id === Auth::id(), 403);

        $stickyNote->update($request->validate([
            'contenu' => ['nullable', 'string'],
            'couleur' => ['nullable', 'string', 'max:9'],
        ]));

        return back();
    }

    public function destroy(StickyNote $stickyNote)
    {
        abort_unless($stickyNote->user_id === Auth::id(), 403);

        $stickyNote->delete();

        return back();
    }
}
