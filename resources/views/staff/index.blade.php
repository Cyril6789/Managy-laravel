@extends('layouts.app')
@section('title', 'Techniciens')

@section('content')
    <x-page-header title="Techniciens" subtitle="Utilisateurs et droits d'accès">
        <x-slot:actions>
            <x-button :href="route('staff.create')"><x-icon name="plus" class="h-4 w-4" /> Nouveau technicien</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card :padding="false">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                <tr>
                    <th class="px-5 py-3 font-medium">Nom</th>
                    <th class="px-5 py-3 font-medium">Identifiant</th>
                    <th class="px-5 py-3 font-medium">Rôle</th>
                    <th class="px-5 py-3 font-medium">État</th>
                    <th class="px-5 py-3 text-right font-medium">Interventions</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($staff as $member)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-xs font-semibold text-white">{{ $member->initials() }}</span>
                                <div><p class="font-medium">{{ $member->fullName() }}</p><p class="text-xs text-gray-400">{{ $member->email }}</p></div>
                            </div>
                        </td>
                        <td class="px-5 py-3">{{ $member->pseudo }}</td>
                        <td class="px-5 py-3">@if ($member->is_admin)<x-badge color="#7c3aed">Administrateur</x-badge>@else<x-badge>Technicien</x-badge>@endif</td>
                        <td class="px-5 py-3">@if ($member->is_active)<x-badge color="#16a34a">Actif</x-badge>@else<x-badge color="#ef4444">Inactif</x-badge>@endif</td>
                        <td class="px-5 py-3 text-right">{{ $member->interventions_count }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('staff.edit', $member) }}" class="text-brand-600 hover:underline">Modifier</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-card>
@endsection
