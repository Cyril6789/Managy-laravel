<?php

namespace App\Livewire;

use App\Models\StickyNote;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Personal sticky notes ("post-it") shown on the dashboard. Fully Livewire:
 * adding, editing (auto-saved on blur) and deleting happen without a page
 * reload, and the delete control is always visible so it works on touch
 * devices (iPad) where there is no hover state.
 */
class StickyNotes extends Component
{
    /** Edit buffers keyed by note id. */
    public array $contenu = [];

    private const PALETTE = ['#fde68a', '#bbf7d0', '#bfdbfe', '#fbcfe8', '#ddd6fe', '#fed7aa'];

    public function mount(): void
    {
        foreach ($this->notes() as $note) {
            $this->contenu[$note->id] = $note->contenu;
        }
    }

    private function notes()
    {
        return StickyNote::where('user_id', Auth::id())->orderBy('ordre')->get();
    }

    public function add(): void
    {
        $count = StickyNote::where('user_id', Auth::id())->count();

        $note = StickyNote::create([
            'user_id' => Auth::id(),
            'contenu' => '',
            'couleur' => self::PALETTE[$count % count(self::PALETTE)],
            'ordre' => (int) StickyNote::where('user_id', Auth::id())->max('ordre') + 1,
        ]);

        $this->contenu[$note->id] = '';
    }

    public function updated($name): void
    {
        // Persist a note as soon as its textarea is synced (blur).
        if (str_starts_with($name, 'contenu.')) {
            $this->save((int) substr($name, strlen('contenu.')));
        }
    }

    public function save(int $id): void
    {
        $note = StickyNote::where('user_id', Auth::id())->find($id);
        $note?->update(['contenu' => $this->contenu[$id] ?? '']);
    }

    public function changeColor(int $id, string $couleur): void
    {
        if (! in_array($couleur, self::PALETTE, true)) {
            return;
        }
        StickyNote::where('user_id', Auth::id())->where('id', $id)->update(['couleur' => $couleur]);
    }

    public function delete(int $id): void
    {
        StickyNote::where('user_id', Auth::id())->where('id', $id)->delete();
        unset($this->contenu[$id]);
    }

    public function render()
    {
        return view('livewire.sticky-notes', [
            'notes' => $this->notes(),
            'palette' => self::PALETTE,
        ]);
    }
}
