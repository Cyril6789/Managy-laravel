@extends('layouts.app')
@section('title', 'Groupes de permissions')

@section('content')
    <x-page-header title="Groupes de permissions" subtitle="Regroupez des droits et affectez-y des utilisateurs">
        <x-slot:actions>
            <x-button :href="route('permission-groups.create')"><x-icon name="plus" class="h-4 w-4" /> Nouveau groupe</x-button>
        </x-slot:actions>
    </x-page-header>

    @include('partials.flash')

    @if ($groups->isEmpty())
        <x-empty-state title="Aucun groupe de permissions"
                       message="Créez un groupe (ex. « Tech_Niv1 »), attribuez-lui des permissions et des utilisateurs. Si le SSO est activé, associez-le à un groupe de sécurité de votre annuaire pour une affectation automatique." />
    @else
        <x-card :padding="false">
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nom</th>
                        <th class="px-5 py-3 text-right font-medium">Permissions</th>
                        <th class="px-5 py-3 text-right font-medium">Utilisateurs</th>
                        <th class="px-5 py-3 text-right font-medium">Groupes SSO</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($groups as $group)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-3">
                                <p class="font-medium">{{ $group->name }}</p>
                                @if ($group->description)<p class="text-xs text-gray-400">{{ $group->description }}</p>@endif
                            </td>
                            <td class="px-5 py-3 text-right">{{ $group->permissions_count }}</td>
                            <td class="px-5 py-3 text-right">{{ $group->users_count }}</td>
                            <td class="px-5 py-3 text-right">{{ $group->sso_mappings_count }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('permission-groups.edit', $group) }}" class="text-brand-600 hover:underline">Modifier</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        </x-card>
    @endif
@endsection
