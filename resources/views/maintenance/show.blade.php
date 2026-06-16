@extends('layouts.app')
@section('title', 'Maintenance · '.$client->nomComplet())

@section('content')
    <x-page-header :title="'Pack maintenance · '.$client->nomComplet()">
        <x-slot:actions>
            <x-button variant="secondary" :href="route('clients.show', $client)">Fiche client</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <x-card>
                <p class="text-sm text-gray-500">Solde actuel</p>
                <p class="mt-1 text-4xl font-bold {{ $solde < 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($solde, 2) }} h</p>
            </x-card>

            @can(\App\Support\Permissions::MAINTENANCE_MANAGE)
                <x-card title="Nouveau mouvement">
                    <form action="{{ route('maintenance.store', $client) }}" method="POST" class="space-y-4">
                        @csrf
                        <x-field label="Type" name="sens" required>
                            <x-select name="sens">
                                <option value="credit">Créditer (+)</option>
                                <option value="debit">Consommer (−)</option>
                            </x-select>
                        </x-field>
                        <x-field label="Heures" name="heures" required><x-input name="heures" type="number" step="0.25" min="0.25" /></x-field>
                        <x-field label="Description" name="description"><x-input name="description" /></x-field>
                        <div class="flex justify-end"><x-button type="submit">Enregistrer</x-button></div>
                    </form>
                </x-card>
            @endcan
        </div>

        <div class="lg:col-span-2">
            <x-card title="Historique" :padding="false">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($mouvements as $m)
                            <tr>
                                <td class="px-5 py-3 text-gray-400">{{ $m->created_at->format('d/m/Y') }}</td>
                                <td class="px-5 py-3">
                                    @if ($m->intervention)
                                        <a href="{{ route('interventions.show', $m->intervention) }}" class="font-medium text-brand-600 hover:underline">{{ $m->intervention->reference }}</a>
                                        @if ($m->description)<span class="text-gray-500"> · {{ $m->description }}</span>@endif
                                    @else
                                        {{ $m->description ?: '—' }}
                                    @endif
                                    <p class="text-xs text-gray-400">{{ $m->user?->fullName() }}</p>
                                </td>
                                <td class="px-5 py-3 text-right font-semibold {{ $m->mouvement < 0 ? 'text-red-600' : 'text-green-600' }}">{{ $m->mouvement > 0 ? '+' : '' }}{{ number_format($m->mouvement, 2) }} h</td>
                                <td class="px-5 py-3 text-right">
                                    @can(\App\Support\Permissions::MAINTENANCE_MANAGE)
                                        <form action="{{ route('maintenance.destroy', $m) }}" method="POST" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')
                                            <button class="text-gray-300 hover:text-red-600">&times;</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-empty-state icon="shield" title="Aucun mouvement" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>
        </div>
    </div>
@endsection
