@extends('admin.layout')

@section('title', $society->name)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-500 hover:text-brand-600">← Retour à la supervision</a>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            @if ($society->logo)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($society->logo) }}" class="h-14 w-14 rounded-xl object-cover" alt="">
            @else
                <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-brand-100 text-lg font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">{{ mb_strtoupper(mb_substr($society->name, 0, 2)) }}</span>
            @endif
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $society->name }}</h1>
                <p class="text-sm text-gray-500">
                    Inscrite le {{ $society->created_at->format('d/m/Y à H:i') }}
                    @if ($society->is_active)
                        · <span class="text-green-600">active</span>
                    @else
                        · <span class="text-red-600">suspendue</span>
                    @endif
                </p>
            </div>
        </div>
        <form action="{{ route('admin.society.toggle', $society) }}" method="POST">
            @csrf
            <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold ring-1 ring-inset {{ $society->is_active ? 'text-red-700 ring-red-300 hover:bg-red-50 dark:text-red-300 dark:ring-red-800' : 'text-green-700 ring-green-300 hover:bg-green-50 dark:text-green-300 dark:ring-green-800' }}">
                {{ $society->is_active ? 'Suspendre la société' : 'Réactiver la société' }}
            </button>
        </form>
    </div>

    {{-- Identity + stats --}}
    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Identité</h2>
            <dl class="mt-4 grid gap-x-6 gap-y-3 sm:grid-cols-2">
                @php
                    $rows = [
                        'E-mail' => $society->email,
                        'Téléphone' => $society->phone,
                        'SIRET' => $society->siret,
                        'TVA' => $society->vat,
                        'Adresse' => trim(($society->address ?? '').' '.($society->postal_code ?? '').' '.($society->city ?? '')),
                        'Site web' => $society->website,
                    ];
                @endphp
                @foreach ($rows as $label => $value)
                    <div>
                        <dt class="text-xs font-medium text-gray-400">{{ $label }}</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500">Interventions</p>
                <p class="mt-1 text-2xl font-bold">{{ number_format($interventions, 0, ',', ' ') }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500">Clients</p>
                <p class="mt-1 text-2xl font-bold">{{ number_format($clients, 0, ',', ' ') }}</p>
            </div>
        </div>
    </div>

    {{-- Users --}}
    <div class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-800">
            <h2 class="text-sm font-semibold">Utilisateurs ({{ $users->count() }})</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                <tr>
                    <th class="px-5 py-3">Nom</th>
                    <th class="px-5 py-3">E-mail</th>
                    <th class="px-5 py-3">Rôle</th>
                    <th class="px-5 py-3">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-5 py-3 font-medium">{{ $user->fullName() }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>
                        <td class="px-5 py-3">{{ $user->is_admin ? 'Gérant' : 'Technicien' }}</td>
                        <td class="px-5 py-3">
                            @if ($user->is_active)
                                <span class="text-green-600">Actif</span>
                            @else
                                <span class="text-gray-400">Désactivé</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
