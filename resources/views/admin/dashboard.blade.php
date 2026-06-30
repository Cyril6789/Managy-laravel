@extends('admin.layout')

@section('title', 'Supervision')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight">Supervision des sociétés</h1>
        <p class="mt-1 text-sm text-gray-500">Vue d'ensemble de toutes les entreprises inscrites sur la plateforme.</p>
    </div>

    {{-- Stat cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $cards = [
                ['Sociétés', $stats['societies'], $stats['active'].' active(s)'],
                ['Utilisateurs', $stats['users'], 'tous comptes confondus'],
                ['Interventions', $stats['interventions'], 'créées au total'],
                ['Clients', $stats['clients'], 'enregistrés au total'],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $hint])
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-bold">{{ number_format($value, 0, ',', ' ') }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    <p class="mt-6 text-sm text-gray-500">
        <span class="font-semibold text-brand-600">{{ $stats['last7days'] }}</span> nouvelle(s) inscription(s) sur les 7 derniers jours.
    </p>

    {{-- Societies table --}}
    <div class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                <tr>
                    <th class="px-5 py-3">Société</th>
                    <th class="px-5 py-3">SIRET</th>
                    <th class="px-5 py-3 text-right">Users</th>
                    <th class="px-5 py-3 text-right">Interv.</th>
                    <th class="px-5 py-3 text-right">Clients</th>
                    <th class="px-5 py-3">Inscription</th>
                    <th class="px-5 py-3">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($societies as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.society', $row['society']) }}" class="flex items-center gap-3 font-medium text-gray-900 hover:text-brand-600 dark:text-gray-100">
                                @if ($row['society']->logo)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($row['society']->logo) }}" class="h-8 w-8 rounded-lg object-cover" alt="">
                                @else
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 text-xs font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">{{ mb_strtoupper(mb_substr($row['society']->name, 0, 2)) }}</span>
                                @endif
                                {{ $row['society']->name }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $row['society']->siret ?: '—' }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $row['users'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $row['interventions'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $row['clients'] }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $row['society']->created_at->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            @if ($row['society']->is_active)
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">Active</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">Suspendue</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.society', $row['society']) }}" class="text-xs font-medium text-brand-600 hover:underline">Détail →</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-gray-400">Aucune société inscrite pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
