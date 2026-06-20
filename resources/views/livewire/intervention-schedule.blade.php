<div class="space-y-4">
    @if ($mode === 'form')
        {{-- Submitted with the surrounding intervention form --}}
        <input type="hidden" name="rdv_debut" value="{{ $rdv_debut }}">
        <input type="hidden" name="rdv_fin" value="{{ $rdv_fin }}">
        @foreach ($selected as $tid)<input type="hidden" name="technicien_ids[]" value="{{ $tid }}">@endforeach
    @endif

    <div class="grid grid-cols-2 gap-3">
        <x-field label="RDV début">
            <x-input type="datetime-local" wire:model.live="rdv_debut" />
        </x-field>
        <x-field label="RDV fin">
            <x-input type="datetime-local" wire:model.live="rdv_fin" />
        </x-field>
    </div>
    @error('rdv_fin')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

    <div>
        <div class="mb-1 flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Technicien(s) affecté(s)</span>
            @if ($hasDay)<span class="text-xs text-gray-400">Disponibilités du jour choisi</span>@endif
        </div>
        <ul class="divide-y divide-gray-100 rounded-lg border border-gray-200 dark:divide-gray-800 dark:border-gray-700">
            @foreach ($technicians as $t)
                <li wire:key="tech-{{ $t['id'] }}" class="flex items-center gap-3 px-3 py-2">
                    <input type="checkbox" wire:click="toggleTechnician({{ $t['id'] }})"
                           @checked(in_array($t['id'], $selected, true))
                           class="rounded border-gray-300 text-brand-600">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-600 text-[10px] font-semibold text-white">{{ $t['initials'] }}</span>
                    <span class="flex-1 text-sm">{{ $t['nom'] }}</span>
                    @if ($hasDay)
                        @if ($t['busy'] === 0)
                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs text-green-700 dark:bg-green-900/30 dark:text-green-300">Libre</span>
                        @else
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
                                  title="{{ collect($t['slots'])->map(fn ($s) => $s['heure'].' '.$s['client'])->implode(', ') }}">
                                {{ $t['busy'] }} RDV
                            </span>
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
        @unless ($hasDay)
            <p class="mt-1 text-xs text-gray-400">Renseignez la date du RDV pour voir les disponibilités de chacun.</p>
        @endunless
    </div>

    @if ($mode === 'live')
        <div class="flex items-center justify-end gap-2">
            <span wire:loading wire:target="save,toggleTechnician" class="text-xs text-amber-600">Enregistrement…</span>
            <span x-data="{ ok: false }" @schedule-saved.window="ok = true; setTimeout(() => ok = false, 1500)"
                  x-show="ok" x-cloak class="text-xs text-green-600">✓ Enregistré</span>
            <x-button type="button" wire:click="save">Enregistrer le RDV</x-button>
        </div>
    @endif
</div>
