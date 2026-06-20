@extends('layouts.app')
@section('title', 'Interventions')

@section('content')
    <x-page-header title="Interventions" :subtitle="$interventions->total().' intervention(s)'">
        <x-slot:actions>
            @can(\App\Support\Permissions::INTERVENTIONS_CREATE)
                <x-button :href="route('interventions.create')"><x-icon name="plus" class="h-4 w-4" /> Nouvelle intervention</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card :padding="false">
        <form method="GET" class="grid grid-cols-1 gap-2 border-b border-gray-100 p-4 dark:border-gray-800 sm:grid-cols-2 lg:grid-cols-5">
            <div class="relative lg:col-span-2">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <x-input name="q" value="{{ request('q') }}" placeholder="Référence, panne, client…" class="pl-9" />
            </div>
            <x-select name="etat" onchange="this.form.submit()">
                <option value="ouvertes" @selected(request('etat','ouvertes')==='ouvertes')>Ouvertes</option>
                <option value="cloturees" @selected(request('etat')==='cloturees')>Clôturées</option>
            </x-select>
            <x-select name="statut" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                @foreach ($statuts as $s)<option value="{{ $s->id }}" @selected(request('statut')==$s->id)>{{ $s->nom }}</option>@endforeach
            </x-select>
            <x-select name="technicien" onchange="this.form.submit()">
                <option value="">Tous les techniciens</option>
                @foreach ($techniciens as $t)<option value="{{ $t->id }}" @selected(request('technicien')==$t->id)>{{ $t->fullName() }}</option>@endforeach
            </x-select>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-5 py-3 font-medium">Réf.</th>
                        <th class="px-5 py-3 font-medium">Lieu</th>
                        <th class="px-5 py-3 font-medium">Client</th>
                        <th class="px-5 py-3 font-medium">Panne</th>
                        <th class="px-5 py-3 font-medium">Techniciens</th>
                        <th class="px-5 py-3 font-medium">Statut</th>
                        <th class="px-5 py-3 font-medium">Ouverte le</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($interventions as $i)
                        <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50" onclick="window.location='{{ route('interventions.show', $i) }}'">
                            <td class="whitespace-nowrap px-5 py-3 font-medium text-brand-600">
                                {{ $i->reference }}
                                @if ($i->urgente)<span class="ml-1 inline-block h-2 w-2 rounded-full bg-red-500" title="Urgent"></span>@endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($i->type_lieu === 'domicile')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-300"><x-icon name="home" class="h-3 w-3" /> Domicile</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-700 dark:bg-sky-900/30 dark:text-sky-300"><x-icon name="wrench" class="h-3 w-3" /> Atelier</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">{{ $i->client?->nomComplet() }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ \Illuminate\Support\Str::limit($i->panne, 50) ?: '—' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex -space-x-1.5">
                                    @foreach ($i->techniciens->take(3) as $t)
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-[10px] font-semibold text-white ring-2 ring-white dark:ring-gray-900" title="{{ $t->fullName() }}">{{ $t->initials() }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-3">@if ($i->statut)<x-badge :color="$i->statut->couleur">{{ $i->statut->nom }}</x-badge>@endif</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-400">{{ $i->opened_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icon="wrench" title="Aucune intervention" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($interventions->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">{{ $interventions->links() }}</div>
        @endif
    </x-card>
@endsection
