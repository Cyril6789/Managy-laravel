@extends('layouts.app')
@section('title', 'Recherche')

@section('content')
    <x-page-header :title="'Recherche'" :subtitle="$q ? 'Résultats pour « '.$q.' »' : null" />

    <form method="GET" class="mb-6">
        <div class="relative max-w-xl">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
            <x-input name="q" value="{{ $q }}" placeholder="Client, intervention…" class="pl-9" autofocus />
        </div>
    </form>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Interventions ({{ $interventions->count() }})" :padding="false">
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($interventions as $i)
                    <a href="{{ route('interventions.show', $i) }}" class="block px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <p class="font-medium text-brand-600">{{ $i->reference }} · {{ $i->client?->nomComplet() }}</p>
                        <p class="truncate text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($i->panne, 60) }}</p>
                    </a>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-gray-400">Aucune intervention</p>
                @endforelse
            </div>
        </x-card>

        <x-card title="Clients ({{ $clients->count() }})" :padding="false">
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($clients as $c)
                    <a href="{{ route('clients.show', $c) }}" class="block px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <p class="font-medium text-brand-600">{{ $c->nomComplet() }}</p>
                        <p class="text-sm text-gray-500">{{ $c->email ?: trim($c->code_postal.' '.$c->ville) }}</p>
                    </a>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-gray-400">Aucun client</p>
                @endforelse
            </div>
        </x-card>
    </div>
@endsection
