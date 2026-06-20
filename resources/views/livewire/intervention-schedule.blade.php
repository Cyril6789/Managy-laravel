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
        <div class="mb-1 flex items-center justify-between gap-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Technicien(s) affecté(s)</span>
            @if ($hasDay)
                <a href="{{ route('disponibilites.index', ['date' => \Illuminate\Support\Carbon::parse($rdv_debut)->toDateString()]) }}"
                   target="_blank" class="text-xs text-brand-600 hover:underline">Voir le planning du jour →</a>
            @endif
        </div>

        @if ($hasDay && $clientVille)
            <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                Intervention à <span class="font-medium">{{ $clientVille }}</span> —
                un badge <span class="rounded bg-violet-100 px-1 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">même ville</span>
                signale un technicien déjà sur place ce jour-là (visites à mutualiser).
            </p>
        @endif

        <ul class="divide-y divide-gray-100 rounded-lg border border-gray-200 dark:divide-gray-800 dark:border-gray-700">
            @foreach ($technicians as $t)
                <li wire:key="tech-{{ $t['id'] }}"
                    class="px-3 py-2.5 {{ $t['unavailable'] ? 'bg-gray-50 dark:bg-gray-800/50' : '' }}">
                    <div class="flex items-center gap-3">
                        @if ($t['unavailable'])
                            <span class="h-4 w-4 shrink-0" title="Indisponible ce jour"></span>
                        @else
                            <input type="checkbox" wire:click="toggleTechnician({{ $t['id'] }})"
                                   @checked(in_array($t['id'], $selected, true))
                                   class="rounded border-gray-300 text-brand-600">
                        @endif
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[10px] font-semibold text-white {{ $t['unavailable'] ? 'bg-gray-400' : 'bg-brand-600' }}">{{ $t['initials'] }}</span>
                        <span class="flex-1 text-sm {{ $t['unavailable'] ? 'text-gray-400 line-through' : '' }}">{{ $t['nom'] }}</span>

                        @if ($hasDay)
                            @if ($t['unavailable'])
                                <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                    Absent{{ count($t['absences']) ? ' · '.$t['absences'][0]['motif'] : '' }}
                                </span>
                            @elseif ($t['same_ville_count'] > 0)
                                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">même ville</span>
                            @elseif ($t['busy'] === 0)
                                <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs text-green-700 dark:bg-green-900/30 dark:text-green-300">Libre</span>
                            @else
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">{{ $t['busy'] }} RDV</span>
                            @endif
                        @endif
                    </div>

                    {{-- Real agenda for the day: each booking's time range + town. --}}
                    @if ($hasDay && (count($t['slots']) || count($t['absences'])))
                        <ul class="mt-1.5 space-y-1 pl-10 text-xs">
                            @foreach ($t['absences'] as $a)
                                <li class="flex items-center gap-1.5 text-red-600 dark:text-red-300">
                                    <span class="font-mono">{{ $a['plage'] }}</span>
                                    <span>· {{ $a['motif'] }}</span>
                                </li>
                            @endforeach
                            @foreach ($t['slots'] as $s)
                                <li class="flex flex-wrap items-center gap-1.5 {{ $s['same_ville'] ? 'text-violet-700 dark:text-violet-300' : 'text-gray-500 dark:text-gray-400' }}">
                                    <span class="font-mono">{{ $s['debut'] }}@if ($s['fin'])–{{ $s['fin'] }}@endif</span>
                                    <span>·</span>
                                    <span class="truncate">{{ $s['client'] }}</span>
                                    @if ($s['ville'])
                                        <span class="inline-flex items-center gap-0.5 {{ $s['same_ville'] ? 'font-medium' : '' }}">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                            {{ $s['ville'] }}
                                        </span>
                                    @endif
                                    @if ($s['domicile'])<span class="text-[10px] uppercase tracking-wide text-gray-400">domicile</span>@endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
        @unless ($hasDay)
            <p class="mt-1 text-xs text-gray-400">Renseignez la date du RDV pour voir le planning et les disponibilités de chacun.</p>
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
