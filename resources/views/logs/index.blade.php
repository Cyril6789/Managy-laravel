@extends('layouts.app')
@section('title', 'Journaux')

@section('content')
    <x-page-header title="Journaux d'activité" />

    <div x-data="{ tab: 'inter' }">
        <div class="mb-4 flex gap-1 border-b border-gray-200 dark:border-gray-800">
            <button @click="tab='inter'" :class="tab==='inter' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-4 py-2 text-sm font-medium">Interventions</button>
            <button @click="tab='app'" :class="tab==='app' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-4 py-2 text-sm font-medium">Connexions & actions</button>
        </div>

        <x-card :padding="false" x-show="tab==='inter'">
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($interLogs as $log)
                    <div class="flex items-center gap-3 px-5 py-2.5 text-sm">
                        <span class="w-32 shrink-0 text-xs text-gray-400">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                        <span class="font-medium">{{ $log->user?->fullName() ?? 'Système' }}</span>
                        <span class="text-gray-600 dark:text-gray-300">{{ $log->texte }}</span>
                        @if ($log->intervention)<a href="{{ route('interventions.show', $log->intervention) }}" class="ml-auto text-brand-600 hover:underline">{{ $log->intervention->reference }}</a>@endif
                    </div>
                @empty
                    <x-empty-state icon="list" title="Aucune activité" />
                @endforelse
            </div>
        </x-card>

        <x-card :padding="false" x-show="tab==='app'" x-cloak>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($appLogs as $log)
                    <div class="flex items-center gap-3 px-5 py-2.5 text-sm">
                        <span class="w-32 shrink-0 text-xs text-gray-400">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                        <span class="font-medium">{{ $log->user?->fullName() ?? '—' }}</span>
                        <span class="text-gray-600 dark:text-gray-300">{{ $log->description ?? $log->action }}</span>
                        <span class="ml-auto text-xs text-gray-400">{{ $log->ip_address }}</span>
                    </div>
                @empty
                    <x-empty-state icon="list" title="Aucune activité" />
                @endforelse
            </div>
        </x-card>
    </div>
@endsection
