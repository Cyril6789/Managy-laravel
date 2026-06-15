<x-card :padding="false">
    <x-slot:title>Post-it</x-slot:title>
    <x-slot:actions>
        <form action="{{ route('sticky.store') }}" method="POST">@csrf
            <button class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800" title="Ajouter">
                <x-icon name="plus" class="h-4 w-4" />
            </button>
        </form>
    </x-slot:actions>

    <div class="space-y-2 p-3">
        @forelse ($postits as $note)
            <div class="group rounded-lg p-3 shadow-sm" style="background-color: {{ $note->couleur }};">
                <form action="{{ route('sticky.update', $note) }}" method="POST" x-data @change.debounce.600ms="$el.requestSubmit()">
                    @csrf @method('PUT')
                    <textarea name="contenu" rows="2" placeholder="Note…"
                              class="w-full resize-none border-0 bg-transparent p-0 text-sm text-gray-800 placeholder-gray-500 focus:ring-0">{{ $note->contenu }}</textarea>
                </form>
                <div class="mt-1 flex items-center justify-end opacity-0 transition group-hover:opacity-100">
                    <form action="{{ route('sticky.destroy', $note) }}" method="POST" onsubmit="return confirm('Supprimer ce post-it ?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-gray-600 hover:text-red-700">Supprimer</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="px-2 py-6 text-center text-sm text-gray-400">Aucun post-it</p>
        @endforelse
    </div>
</x-card>
