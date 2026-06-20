<div>
    <x-page-header title="Disponibilités des techniciens">
        <x-slot:actions>
            <x-button type="button" wire:click="openForm()">+ Déclarer une absence</x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Day navigation --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <button type="button" wire:click="prevDay" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">‹</button>
            <input type="date" wire:model.live="date" class="rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
            <button type="button" wire:click="nextDay" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">›</button>
            <button type="button" wire:click="goToday" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Aujourd'hui</button>
        </div>
        <p class="text-sm font-medium capitalize text-gray-600 dark:text-gray-300">
            {{ $day->translatedFormat('l j F Y') }}
        </p>
    </div>

    {{-- Pooling hint: towns with several on-site visits the same day --}}
    @if ($villesDuJour->isNotEmpty())
        <div class="mb-5 rounded-lg border border-violet-200 bg-violet-50 px-4 py-3 text-sm dark:border-violet-800 dark:bg-violet-900/20">
            <span class="font-medium text-violet-800 dark:text-violet-200">Visites à mutualiser :</span>
            <span class="text-violet-700 dark:text-violet-300">
                @foreach ($villesDuJour as $ville => $n)
                    {{ $ville }} ({{ $n }}){{ ! $loop->last ? ',' : '' }}
                @endforeach
                — plusieurs interventions à domicile dans la même ville ce jour-là.
            </span>
        </div>
    @endif

    {{-- Absence form --}}
    @if ($showForm)
        <x-card class="mb-5" title="Déclarer une absence">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @if ($this->canManageOthers())
                    <x-field label="Technicien">
                        <x-select wire:model="absenceUserId">
                            @foreach ($technicians as $u)
                                <option value="{{ $u->id }}">{{ $u->fullName() }}</option>
                            @endforeach
                        </x-select>
                    </x-field>
                @else
                    <div></div>
                @endif

                <x-field label="Motif">
                    <x-select wire:model="absMotif">
                        @foreach ($motifs as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                    </x-select>
                </x-field>

                <label class="md:col-span-2 flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.live="absJournee" class="rounded border-gray-300 text-brand-600">
                    Journée(s) entière(s)
                </label>

                <x-field label="{{ $absJournee ? 'Du' : 'Début' }}">
                    <x-input type="{{ $absJournee ? 'date' : 'datetime-local' }}" wire:model="absDebut" />
                    @error('absDebut')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </x-field>
                <x-field label="{{ $absJournee ? 'Au' : 'Fin' }}">
                    <x-input type="{{ $absJournee ? 'date' : 'datetime-local' }}" wire:model="absFin" />
                    @error('absFin')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </x-field>

                <x-field label="Note (facultatif)" class="md:col-span-2">
                    <x-input wire:model="absNote" placeholder="Précision interne…" />
                </x-field>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <x-button type="button" variant="secondary" wire:click="$set('showForm', false)">Annuler</x-button>
                <x-button type="button" wire:click="addAbsence">Enregistrer l'absence</x-button>
            </div>
        </x-card>
    @endif

    {{-- Per-technician planning board --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3">
        @foreach ($board as $t)
            <div wire:key="board-{{ $t['id'] }}"
                 class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-900 {{ $t['absent'] ? 'border-red-200 dark:border-red-900/50' : 'border-gray-200 dark:border-gray-800' }}">
                <div class="mb-3 flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold text-white {{ $t['absent'] ? 'bg-gray-400' : 'bg-brand-600' }}">{{ $t['initials'] }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium">{{ $t['nom'] }} @if ($t['is_self'])<span class="text-xs text-gray-400">(vous)</span>@endif</p>
                        <p class="text-xs text-gray-400">
                            {{ count($t['slots']) }} RDV
                            @if ($t['absent']) · <span class="text-red-500">absent</span>@endif
                        </p>
                    </div>
                    @if ($t['is_self'] || $this->canManageOthers())
                        @unless ($t['absent'])
                            <button type="button" wire:click="markAbsentToday({{ $t['id'] }})"
                                    class="shrink-0 rounded-md border border-red-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:hover:bg-red-900/20"
                                    title="Marquer absent ce jour">Absent ce jour</button>
                        @endunless
                    @endif
                </div>

                {{-- Absences --}}
                @foreach ($t['absences'] as $a)
                    <div class="mb-1.5 flex items-center justify-between gap-2 rounded-md bg-red-50 px-2.5 py-1.5 text-xs dark:bg-red-900/20">
                        <span class="text-red-700 dark:text-red-300">
                            <span class="font-medium">{{ $a['motif'] }}</span> · {{ $a['plage'] }}
                            @if ($a['note'])<span class="text-red-400"> — {{ $a['note'] }}</span>@endif
                        </span>
                        @if ($t['is_self'] || $this->canManageOthers())
                            <button type="button" wire:click="deleteAbsence({{ $a['id'] }})"
                                    wire:confirm="Supprimer cette absence ?"
                                    class="shrink-0 text-red-400 hover:text-red-600" title="Supprimer">✕</button>
                        @endif
                    </div>
                @endforeach

                {{-- Bookings --}}
                @forelse ($t['slots'] as $s)
                    <a href="{{ $s['url'] }}"
                       class="mb-1.5 flex items-start gap-2 rounded-md border border-gray-100 px-2.5 py-1.5 text-xs hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800">
                        <span class="shrink-0 font-mono text-gray-500 dark:text-gray-400">{{ $s['debut'] }}@if ($s['fin'])<br>{{ $s['fin'] }}@endif</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-medium">{{ $s['client'] }}</span>
                            <span class="flex flex-wrap items-center gap-1 text-gray-400">
                                @if ($s['reference'])<span>{{ $s['reference'] }}</span>@endif
                                @if ($s['ville'])
                                    <span class="inline-flex items-center gap-0.5">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                        {{ $s['ville'] }}
                                    </span>
                                @endif
                                @if ($s['domicile'])<span class="text-[10px] uppercase tracking-wide">domicile</span>@endif
                            </span>
                        </span>
                    </a>
                @empty
                    @if (! $t['absent'])
                        <p class="text-xs text-gray-400">Aucun rendez-vous ce jour.</p>
                    @endif
                @endforelse
            </div>
        @endforeach
    </div>
</div>
