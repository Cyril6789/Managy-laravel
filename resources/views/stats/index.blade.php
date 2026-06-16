@extends('layouts.app')
@section('title', 'Statistiques')

@section('content')
    <x-page-header title="Statistiques">
        <x-slot:actions>
            <form method="GET" class="flex items-end gap-2">
                <x-field label="Du"><x-input name="from" type="date" value="{{ $from->format('Y-m-d') }}" /></x-field>
                <x-field label="Au"><x-input name="to" type="date" value="{{ $to->format('Y-m-d') }}" /></x-field>
                <x-button type="submit">Appliquer</x-button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([['Interventions', $totaux['total']], ['Clôturées', $totaux['cloturees']], ['Heures saisies', rtrim(rtrim(number_format($totaux['heures'], 2), '0'), '.')], ['CA estimé', number_format($totaux['ca_estime'], 0, ',', ' ').' €']] as [$label, $value])
            <x-card><p class="text-2xl font-bold">{{ $value }}</p><p class="text-xs text-gray-500">{{ $label }}</p></x-card>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Interventions par mois">
            @php $maxMois = max(1, $parMois->max('ouvertes')); @endphp
            <div class="flex h-48 items-end gap-3">
                @foreach ($parMois as $m)
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <div class="flex w-full items-end justify-center gap-1" style="height: 150px;">
                            <div class="w-3 rounded-t bg-brand-500" style="height: {{ round($m['ouvertes'] / $maxMois * 100) }}%;" title="{{ $m['ouvertes'] }} ouvertes"></div>
                            <div class="w-3 rounded-t bg-green-500" style="height: {{ round($m['cloturees'] / $maxMois * 100) }}%;" title="{{ $m['cloturees'] }} clôturées"></div>
                        </div>
                        <span class="text-[10px] text-gray-400">{{ $m['label'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 flex gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-brand-500"></span> Ouvertes</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-green-500"></span> Clôturées</span>
            </div>
        </x-card>

        <x-card title="Heures par technicien">
            @php $maxH = max(1, $heuresParTech->max('heures')); @endphp
            <div class="space-y-2">
                @forelse ($heuresParTech as $t)
                    <div class="flex items-center gap-3">
                        <span class="w-32 shrink-0 truncate text-sm">{{ $t->prenom }} {{ $t->nom }}</span>
                        <div class="h-3 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-brand-500" style="width: {{ round($t->heures / $maxH * 100) }}%;"></div>
                        </div>
                        <span class="w-12 shrink-0 text-right text-sm">{{ rtrim(rtrim(number_format($t->heures, 1), '0'), '.') }} h</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Aucune donnée</p>
                @endforelse
            </div>
        </x-card>
    </div>
@endsection
