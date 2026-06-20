<div>
    <x-card :title="$title" :padding="false">
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($items as $item)
                <div wire:key="ref-{{ $type }}-{{ $item->id }}" class="px-5 py-2">
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach ($fields as $f)
                            @if ($f['kind'] === 'textarea')
                                @continue
                            @elseif ($f['kind'] === 'color')
                                <input type="color" wire:model="rows.{{ $item->id }}.{{ $f['key'] }}" class="h-9 w-10 rounded border-gray-300">
                            @elseif ($f['kind'] === 'check')
                                <label class="flex items-center gap-1 text-xs">
                                    <input type="checkbox" wire:model="rows.{{ $item->id }}.{{ $f['key'] }}" class="rounded border-gray-300 text-brand-600"> {{ $f['label'] }}
                                </label>
                            @elseif ($f['kind'] === 'number')
                                <x-input type="number" step="{{ $f['step'] ?? '1' }}" wire:model="rows.{{ $item->id }}.{{ $f['key'] }}" placeholder="{{ $f['placeholder'] ?? '' }}" class="{{ $f['class'] ?? 'w-24' }}" />
                            @else
                                <x-input wire:model="rows.{{ $item->id }}.{{ $f['key'] }}" placeholder="{{ $f['placeholder'] ?? '' }}" class="{{ $f['class'] ?? 'flex-1' }}" />
                            @endif
                        @endforeach
                        <x-button variant="secondary" type="button" wire:click="save({{ $item->id }})">OK</x-button>
                        <span x-data="{ ok: false }" @reference-saved.window="if ($event.detail.id === {{ $item->id }}) { ok = true; setTimeout(() => ok = false, 1200) }"
                              x-show="ok" x-cloak class="text-xs text-green-600">✓</span>
                        <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Supprimer ?" class="px-1 text-gray-300 hover:text-red-600">&times;</button>
                    </div>
                    @foreach ($fields as $f)
                        @if ($f['kind'] === 'textarea')
                            <x-textarea wire:model="rows.{{ $item->id }}.{{ $f['key'] }}" rows="2" class="mt-2" />
                        @endif
                    @endforeach
                    @error('rows.'.$item->id.'.'.$fields[0]['key'])<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            @empty
                <p class="px-5 py-6 text-center text-sm text-gray-400">Aucune entrée.</p>
            @endforelse
        </div>

        {{-- Create --}}
        <div class="border-t border-gray-100 p-4 dark:border-gray-800">
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($fields as $f)
                    @if ($f['kind'] === 'textarea')
                        @continue
                    @elseif ($f['kind'] === 'color')
                        <input type="color" wire:model="draft.{{ $f['key'] }}" class="h-9 w-10 rounded border-gray-300">
                    @elseif ($f['kind'] === 'check')
                        <label class="flex items-center gap-1 text-xs">
                            <input type="checkbox" wire:model="draft.{{ $f['key'] }}" class="rounded border-gray-300 text-brand-600"> {{ $f['label'] }}
                        </label>
                    @elseif ($f['kind'] === 'number')
                        <x-input type="number" step="{{ $f['step'] ?? '1' }}" wire:model="draft.{{ $f['key'] }}" placeholder="{{ $f['placeholder'] ?? '' }}" class="{{ $f['class'] ?? 'w-24' }}" />
                    @else
                        <x-input wire:model="draft.{{ $f['key'] }}" placeholder="{{ $f['placeholder'] ?? '' }}" class="{{ $f['class'] ?? 'flex-1' }}" />
                    @endif
                @endforeach
                <x-button type="button" wire:click="create"><x-icon name="plus" class="h-4 w-4" /></x-button>
            </div>
            @foreach ($fields as $f)
                @if ($f['kind'] === 'textarea')
                    <x-textarea wire:model="draft.{{ $f['key'] }}" rows="2" placeholder="{{ $f['placeholder'] ?? '' }}" class="mt-2" />
                @endif
            @endforeach
            @error('draft.'.$fields[0]['key'])<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </x-card>
</div>
