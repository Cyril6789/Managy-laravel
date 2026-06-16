@extends('layouts.app')
@section('title', 'Tableau de bord')

@section('content')
    <x-page-header title="Tableau de bord" :subtitle="'Bonjour '.auth()->user()->prenom.', voici votre activité du jour.'">
        <x-slot:actions>
            @can(\App\Support\Permissions::INTERVENTIONS_CREATE)
                <x-button :href="route('interventions.create')"><x-icon name="plus" class="h-4 w-4" /> Nouvelle intervention</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php
            $factUrl = auth()->user()->can(\App\Support\Permissions::INTERVENTIONS_FACTURATION) ? route('facturation.index') : null;
            $kpis = [
                ['Interventions ouvertes', $stats['ouvertes'], 'wrench', 'text-brand-600', null],
                ['Urgentes', $stats['urgentes'], 'bolt', 'text-red-600', null],
                ['Clôturées ce mois', $stats['cloturees_mois'], 'check', 'text-green-600', null],
                ['À facturer', $stats['a_facturer'], 'list', 'text-amber-600', $factUrl],
            ];
        @endphp
        @foreach ($kpis as [$label, $value, $icon, $color, $url])
            <x-card :class="$url ? 'transition hover:shadow-md' : ''">
                <a @if ($url) href="{{ $url }}" @endif class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gray-100 {{ $color }} dark:bg-gray-800">
                        <x-icon :name="$icon" class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $value }}</p>
                        <p class="text-xs text-gray-500">{{ $label }}</p>
                    </div>
                </a>
            </x-card>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- My interventions --}}
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Mes interventions en cours" :padding="false">
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($mesInterventions as $i)
                        <a href="{{ route('interventions.show', $i) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ $i->reference }} · {{ $i->client?->nomComplet() }}</p>
                                <p class="truncate text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($i->panne, 70) ?: '—' }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                @if ($i->urgente)<x-badge color="#ef4444">Urgent</x-badge>@endif
                                @if ($i->statut)<x-badge :color="$i->statut->couleur">{{ $i->statut->nom }}</x-badge>@endif
                            </div>
                        </a>
                    @empty
                        <x-empty-state icon="wrench" title="Aucune intervention en cours" message="Vous n'êtes assigné à aucune intervention ouverte." />
                    @endforelse
                </div>
            </x-card>

            <x-card title="Interventions ouvertes par statut">
                <div class="space-y-2">
                    @php $maxStatut = max(1, $parStatut->max('interventions_count')); @endphp
                    @foreach ($parStatut as $s)
                        <div class="flex items-center gap-3">
                            <span class="w-32 shrink-0 truncate text-sm">{{ $s->nom }}</span>
                            <div class="h-3 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full rounded-full" style="width: {{ round($s->interventions_count / $maxStatut * 100) }}%; background-color: {{ $s->couleur }};"></div>
                            </div>
                            <span class="w-8 shrink-0 text-right text-sm font-medium">{{ $s->interventions_count }}</span>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>

        {{-- Right column: today's appointments + tasks + sticky --}}
        <div class="space-y-6">
            <x-card title="Rendez-vous du jour" :padding="false">
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($rdvJour as $i)
                        <a href="{{ route('interventions.show', $i) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <span class="font-mono text-sm font-semibold text-brand-600">{{ $i->rdv_debut->format('H:i') }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm">{{ $i->client?->nomComplet() }}</p>
                                @if ($i->type_lieu === 'domicile' && ($i->client?->ville || $i->client?->code_postal))
                                    <p class="truncate text-xs text-gray-400">📍 {{ trim($i->client->code_postal.' '.$i->client->ville) }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 text-xs {{ $i->type_lieu === 'domicile' ? 'font-medium text-amber-600' : 'text-gray-400' }}">{{ $i->type_lieu === 'domicile' ? 'Domicile' : 'Atelier' }}</span>
                        </a>
                    @empty
                        <x-empty-state icon="calendar" title="Aucun rendez-vous aujourd'hui" />
                    @endforelse
                </div>
            </x-card>

            <x-card title="Mes tâches" :padding="false">
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($mesTaches as $t)
                        <form action="{{ route('tasks.toggle', $t) }}" method="POST" class="flex items-center gap-3 px-5 py-2.5">@csrf
                            <button class="flex h-4 w-4 shrink-0 items-center justify-center rounded border border-gray-300 dark:border-gray-600"></button>
                            <span class="truncate text-sm">{{ $t->titre }}</span>
                            @if ($t->echeance)<span class="ml-auto text-xs {{ $t->echeance->isPast() ? 'text-red-500' : 'text-gray-400' }}">{{ $t->echeance->format('d/m') }}</span>@endif
                        </form>
                    @empty
                        <x-empty-state icon="check" title="Aucune tâche en attente" />
                    @endforelse
                </div>
            </x-card>

            @include('partials.stickies', ['postits' => $postits])
        </div>
    </div>
@endsection
