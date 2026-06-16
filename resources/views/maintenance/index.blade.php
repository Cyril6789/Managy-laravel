@extends('layouts.app')
@section('title', 'Pack maintenance')

@section('content')
    <x-page-header title="Pack maintenance" subtitle="Solde d'heures par client" />

    <x-card :padding="false">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                <tr><th class="px-5 py-3 font-medium">Client</th><th class="px-5 py-3 text-right font-medium">Solde (h)</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($clients as $client)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-5 py-3"><a href="{{ route('maintenance.show', $client) }}" class="font-medium text-brand-600 hover:underline">{{ $client->nomComplet() }}</a></td>
                        <td class="px-5 py-3 text-right font-semibold {{ $client->solde < 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($client->solde, 2) }}</td>
                        <td class="px-5 py-3 text-right"><a href="{{ route('maintenance.show', $client) }}" class="text-sm text-brand-600 hover:underline">Détail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-empty-state icon="shield" title="Aucun pack maintenance" message="Créditez un client depuis sa fiche maintenance." /></td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($clients->hasPages())<div class="border-t border-gray-100 p-4 dark:border-gray-800">{{ $clients->links() }}</div>@endif
    </x-card>
@endsection
