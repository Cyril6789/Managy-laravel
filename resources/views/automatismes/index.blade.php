@extends('layouts.app')
@section('title', 'Automatismes')

@section('content')
    <x-page-header title="Automatismes" subtitle="Envois SMS / e-mail automatiques sur événement">
        <x-slot:actions>
            <x-button :href="route('automatismes.create')"><x-icon name="plus" class="h-4 w-4" /> Nouvel automatisme</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card :padding="false">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                <tr><th class="px-5 py-3 font-medium">Libellé</th><th class="px-5 py-3 font-medium">Événement</th><th class="px-5 py-3 font-medium">Canal</th><th class="px-5 py-3 font-medium">État</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($automatismes as $a)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-5 py-3 font-medium">{{ $a->libelle }}</td>
                        <td class="px-5 py-3">{{ $events[$a->evenement] ?? $a->evenement }}@if ($a->statut) <span class="text-xs text-gray-400">({{ $a->statut->nom }})</span>@endif</td>
                        <td class="px-5 py-3"><x-badge>{{ strtoupper($a->canal) }}</x-badge></td>
                        <td class="px-5 py-3">@if ($a->actif)<x-badge color="#16a34a">Actif</x-badge>@else<x-badge color="#ef4444">Inactif</x-badge>@endif</td>
                        <td class="px-5 py-3 text-right"><a href="{{ route('automatismes.edit', $a) }}" class="text-brand-600 hover:underline">Modifier</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state icon="bolt" title="Aucun automatisme" message="Créez un envoi automatique, par ex. un SMS à la clôture." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
@endsection
