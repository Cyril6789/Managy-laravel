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

    {{-- Billed averages over closed interventions --}}
    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php
            $fmt = fn ($v, $d = 1) => rtrim(rtrim(number_format($v, $d), '0'), '.');
        @endphp
        @foreach ([
            ['Durée moy. facturée', $fmt($moyennes['duree']).' h', 'par intervention clôturée'],
            ['Pièces moy. facturées', number_format($moyennes['pieces'], 0, ',', ' ').' €', 'par intervention clôturée'],
            ['Panier moyen', number_format($moyennes['panier'], 0, ',', ' ').' €', 'total TTC par intervention'],
            ['Délai moyen', $fmt($moyennes['delai']).' j', 'ouverture → clôture'],
        ] as [$label, $value, $hint])
            <x-card>
                <p class="text-2xl font-bold">{{ $value }}</p>
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="text-[10px] text-gray-400">{{ $hint }}</p>
            </x-card>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Répartition par type de machine">
            @php $maxMat = max(1, $parMateriel->max('total')); @endphp
            <div class="space-y-2">
                @forelse ($parMateriel as $row)
                    <div class="flex items-center gap-3">
                        <span class="w-32 shrink-0 truncate text-sm">{{ $row->label }}</span>
                        <div class="h-3 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-brand-500" style="width: {{ round($row->total / $maxMat * 100) }}%;"></div>
                        </div>
                        <span class="w-10 shrink-0 text-right text-sm">{{ $row->total }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Aucune donnée</p>
                @endforelse
            </div>
        </x-card>

        <x-card title="Type d'intervention">
            @php $maxLieu = max(1, $parLieu->max('total')); @endphp
            <div class="space-y-2">
                @forelse ($parLieu as $row)
                    <div class="flex items-center gap-3">
                        <span class="w-32 shrink-0 truncate text-sm">{{ $row['label'] }}</span>
                        <div class="h-3 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-indigo-500" style="width: {{ round($row['total'] / $maxLieu * 100) }}%;"></div>
                        </div>
                        <span class="w-10 shrink-0 text-right text-sm">{{ $row['total'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Aucune donnée</p>
                @endforelse
            </div>
        </x-card>

        <x-card title="Répartition par statut">
            @php $maxStatut = max(1, $parStatut->max('total')); @endphp
            <div class="space-y-2">
                @forelse ($parStatut as $row)
                    <div class="flex items-center gap-3">
                        <span class="flex w-32 shrink-0 items-center gap-1.5 truncate text-sm">
                            <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $row->couleur }};"></span>
                            {{ $row->label }}
                        </span>
                        <div class="h-3 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full" style="width: {{ round($row->total / $maxStatut * 100) }}%; background-color: {{ $row->couleur }};"></div>
                        </div>
                        <span class="w-10 shrink-0 text-right text-sm">{{ $row->total }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Aucune donnée</p>
                @endforelse
            </div>
        </x-card>

        <x-card title="Chiffre d'affaires facturé par mois">
            @php $maxCa = max(1, $caParMois->max(fn ($m) => $m['prestations'] + $m['pieces'])); @endphp
            <div class="flex h-48 items-end gap-3">
                @foreach ($caParMois as $m)
                    @php $tot = $m['prestations'] + $m['pieces']; @endphp
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <div class="flex w-full flex-col-reverse items-center" style="height: 150px;">
                            <div class="w-6 rounded-b bg-brand-500" style="height: {{ round($m['prestations'] / $maxCa * 150) }}px;" title="Prestations : {{ number_format($m['prestations'], 0, ',', ' ') }} €"></div>
                            <div class="w-6 rounded-t bg-amber-500" style="height: {{ round($m['pieces'] / $maxCa * 150) }}px;" title="Pièces : {{ number_format($m['pieces'], 0, ',', ' ') }} €"></div>
                        </div>
                        <span class="text-[10px] text-gray-400">{{ $m['label'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 flex gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-brand-500"></span> Prestations</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Pièces</span>
            </div>
        </x-card>
    </div>
@endsection
