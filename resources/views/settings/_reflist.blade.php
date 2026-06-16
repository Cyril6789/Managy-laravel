{{-- Simple name-only reference list. Params: $type (slug), $field, $items, $label, $placeholder --}}
<x-card :title="$label" :padding="false">
    <div class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse ($items as $item)
            <div class="flex items-center gap-2 px-5 py-2">
                <form action="{{ route('settings.reference.update', [$type, $item->id]) }}" method="POST" class="flex flex-1 items-center gap-2">
                    @csrf @method('PUT')
                    <x-input name="{{ $field }}" value="{{ $item->{$field} }}" class="flex-1" />
                    <x-button variant="secondary" type="submit">OK</x-button>
                </form>
                <form action="{{ route('settings.reference.destroy', [$type, $item->id]) }}" method="POST" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')
                    <button class="px-2 text-gray-300 hover:text-red-600">&times;</button>
                </form>
            </div>
        @empty
            <p class="px-5 py-4 text-sm text-gray-400">Aucune entrée</p>
        @endforelse
    </div>
    <form action="{{ route('settings.reference.store', $type) }}" method="POST" class="flex gap-2 border-t border-gray-100 p-4 dark:border-gray-800">
        @csrf
        <x-input name="{{ $field }}" placeholder="{{ $placeholder ?? 'Ajouter…' }}" class="flex-1" />
        <x-button type="submit"><x-icon name="plus" class="h-4 w-4" /></x-button>
    </form>
</x-card>
