@extends('layouts.app')
@section('title', 'Calendrier')

@section('content')
    <x-page-header :title="ucfirst($cursor->translatedFormat('F Y'))">
        <x-slot:actions>
            <x-button variant="secondary" :href="route('calendar.index', ['mois' => $cursor->copy()->subMonth()->format('Y-m')])">←</x-button>
            <x-button variant="secondary" :href="route('calendar.index')">Aujourd'hui</x-button>
            <x-button variant="secondary" :href="route('calendar.index', ['mois' => $cursor->copy()->addMonth()->format('Y-m')])">→</x-button>
            @can(\App\Support\Permissions::CALENDAR_MANAGE)
                <div x-data="{ open: false }">
                    <x-button @click="open = true"><x-icon name="plus" class="h-4 w-4" /> Rendez-vous</x-button>
                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="open = false">
                        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                            <h3 class="mb-4 text-lg font-semibold">Nouveau rendez-vous</h3>
                            <form action="{{ route('calendar.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <x-field label="Titre" name="titre" required><x-input name="titre" /></x-field>
                                <div class="grid grid-cols-2 gap-3">
                                    <x-field label="Début" name="debut" required><x-input name="debut" type="datetime-local" /></x-field>
                                    <x-field label="Fin" name="fin"><x-input name="fin" type="datetime-local" /></x-field>
                                </div>
                                <x-field label="Client" name="client_id">
                                    <x-select name="client_id"><option value="">—</option>
                                        @foreach ($clients as $c)<option value="{{ $c->id }}">{{ $c->nomComplet() }}</option>@endforeach
                                    </x-select>
                                </x-field>
                                <x-field label="Description" name="description"><x-textarea name="description" rows="2" /></x-field>
                                <div class="flex justify-end gap-2">
                                    <x-button type="button" variant="secondary" @click="open = false">Annuler</x-button>
                                    <x-button type="submit">Ajouter</x-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card :padding="false">
        <div class="grid grid-cols-7 border-b border-gray-100 text-center text-xs font-medium uppercase text-gray-400 dark:border-gray-800">
            @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $j)
                <div class="py-2">{{ $j }}</div>
            @endforeach
        </div>
        <div class="grid grid-cols-7">
            @foreach ($weeks as $week)
                @foreach ($week as $day)
                    <div class="min-h-28 border-b border-r border-gray-100 p-1.5 dark:border-gray-800 {{ $day['inMonth'] ? '' : 'bg-gray-50/60 dark:bg-gray-800/30' }}">
                        <div class="mb-1 text-right text-xs {{ $day['isToday'] ? 'font-bold text-brand-600' : ($day['inMonth'] ? 'text-gray-500' : 'text-gray-300') }}">
                            @if ($day['isToday'])
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-brand-600 text-white">{{ $day['date']->day }}</span>
                            @else {{ $day['date']->day }} @endif
                        </div>
                        <div class="space-y-1">
                            @foreach ($day['items']->take(4) as $item)
                                <a @if ($item['url']) href="{{ $item['url'] }}" @endif
                                   class="block truncate rounded px-1.5 py-0.5 text-[11px] text-white" style="background-color: {{ $item['couleur'] }};">
                                    {{ $item['date']->format('H:i') }} {{ $item['titre'] }}
                                </a>
                            @endforeach
                            @if ($day['items']->count() > 4)
                                <p class="px-1 text-[10px] text-gray-400">+{{ $day['items']->count() - 4 }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </x-card>
@endsection
