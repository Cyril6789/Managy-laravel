@extends('layouts.app')
@section('title', 'Clients')

@section('content')
    <x-page-header title="Clients" :subtitle="$clients->total().' client(s)'">
        <x-slot:actions>
            @can(\App\Support\Permissions::CLIENTS_MANAGE)
                <x-button :href="route('clients.create')"><x-icon name="plus" class="h-4 w-4" /> Nouveau client</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card :padding="false">
        <form method="GET" class="flex flex-wrap items-center gap-2 border-b border-gray-100 p-4 dark:border-gray-800">
            <div class="relative flex-1 min-w-48">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <x-input name="q" value="{{ request('q') }}" placeholder="Rechercher…" class="pl-9" />
            </div>
            <x-select name="type" class="w-44" onchange="this.form.submit()">
                <option value="">Tous les types</option>
                <option value="professionnel" @selected(request('type')==='professionnel')>Professionnels</option>
                <option value="particulier" @selected(request('type')==='particulier')>Particuliers</option>
            </x-select>
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" name="archived" value="1" @checked(request('archived')) onchange="this.form.submit()" class="rounded border-gray-300 text-brand-600">
                Inclure archivés
            </label>
            <x-button variant="secondary" type="submit">Filtrer</x-button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nom</th>
                        <th class="px-5 py-3 font-medium">Type</th>
                        <th class="px-5 py-3 font-medium">Localité</th>
                        <th class="px-5 py-3 font-medium">Téléphone</th>
                        <th class="px-5 py-3 text-right font-medium">Interventions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($clients as $client)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-3">
                                <a href="{{ route('clients.show', $client) }}" class="font-medium text-brand-600 hover:underline">{{ $client->nomComplet() }}</a>
                                @if ($client->archived_at)<x-badge class="ml-1">Archivé</x-badge>@endif
                                @if ($client->email)<p class="text-xs text-gray-400">{{ $client->email }}</p>@endif
                            </td>
                            <td class="px-5 py-3">{{ $client->type === 'professionnel' ? 'Pro' : 'Particulier' }}</td>
                            <td class="px-5 py-3">{{ trim($client->code_postal.' '.$client->ville) ?: '—' }}</td>
                            <td class="px-5 py-3">{{ $client->telephone_mobile ?: $client->telephone_fixe ?: '—' }}</td>
                            <td class="px-5 py-3 text-right">{{ $client->interventions_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="users" title="Aucun client" message="Créez votre premier client pour démarrer." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($clients->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">{{ $clients->links() }}</div>
        @endif
    </x-card>
@endsection
