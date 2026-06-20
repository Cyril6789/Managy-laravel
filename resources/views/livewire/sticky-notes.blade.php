<div>
    <x-card :padding="false">
        <x-slot:title>Post-it</x-slot:title>
        <x-slot:actions>
            <button type="button" wire:click="add" wire:loading.attr="disabled"
                    class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800" title="Ajouter un post-it">
                <x-icon name="plus" class="h-4 w-4" />
            </button>
        </x-slot:actions>

        <div class="space-y-2 p-3">
            @forelse ($notes as $note)
                <div wire:key="note-{{ $note->id }}" class="rounded-lg p-3 shadow-sm" style="background-color: {{ $note->couleur }};">
                    <textarea wire:model.blur="contenu.{{ $note->id }}" rows="2" placeholder="Note…"
                              class="w-full resize-none border-0 bg-transparent p-0 text-sm text-gray-800 placeholder-gray-500 focus:ring-0"></textarea>

                    <div class="mt-2 flex items-center justify-between gap-2">
                        {{-- Colour swatches --}}
                        <div class="flex items-center gap-1">
                            @foreach ($palette as $couleur)
                                <button type="button" wire:click="changeColor({{ $note->id }}, '{{ $couleur }}')"
                                        class="h-4 w-4 rounded-full border border-black/10 {{ $note->couleur === $couleur ? 'ring-2 ring-gray-700/40' : '' }}"
                                        style="background-color: {{ $couleur }};" title="Changer la couleur"></button>
                            @endforeach
                        </div>
                        {{-- Always-visible delete (works on touch / iPad) --}}
                        <button type="button" wire:click="delete({{ $note->id }})"
                                wire:confirm="Supprimer ce post-it ?"
                                class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs text-gray-700 hover:bg-black/5 hover:text-red-700">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 9m-4.8 0L9.26 9m9.97-3.23c.34.05.68.1 1.02.16M3.75 5.79c.34-.06.68-.11 1.02-.16m14.46 0a48.1 48.1 0 0 0-3.48-.4m-12 .56a48.1 48.1 0 0 1 3.48-.4m9.04 0V4.48c0-1.14-.88-2.09-2.01-2.13a51.2 51.2 0 0 0-3.46 0c-1.13.04-2.01.99-2.01 2.13v1.06"/></svg>
                            Supprimer
                        </button>
                    </div>
                </div>
            @empty
                <p class="px-2 py-6 text-center text-sm text-gray-400">Aucun post-it</p>
            @endforelse
        </div>
    </x-card>
</div>
