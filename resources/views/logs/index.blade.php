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
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 px-4 py-2.5 text-sm sm:px-5">
                        <span class="text-xs text-gray-400">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
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
            @php
                $actionMeta = [
                    'created' => ['Création', 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300'],
                    'updated' => ['Modification', 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'],
                    'deleted' => ['Suppression', 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300'],
                    'restored' => ['Restauration', 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'],
                ];
                // Technical columns not worth showing in the human-readable detail.
                $hideKeys = ['id', 'society_id', 'created_at', 'updated_at', 'public_token', 'signature_path', 'remember_token'];
                $fmt = fn ($v) => $v === null || $v === '' ? '—' : \Illuminate\Support\Str::limit(is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string) $v, 80);
            @endphp
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($appLogs as $log)
                    @php
                        $isUpdate = $log->action === 'updated' && ! empty($log->changes['new']);
                        $snapshot = collect($log->changes['attributes'] ?? [])->except($hideKeys)->filter(fn ($v) => $v !== null && $v !== '');
                        $hasDetail = $isUpdate || $snapshot->isNotEmpty();
                        // A deleted subject is trashed, so a "Voir" link would 404 — offer it only when the record exists.
                        $canView = in_array($log->action, ['created', 'updated', 'restored']) && $log->subjectUrl();
                    @endphp
                    <div class="px-4 py-2.5 text-sm sm:px-5" x-data="{ open: false }">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span class="text-xs text-gray-400">{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                            <span class="font-medium">{{ $log->user?->fullName() ?? '—' }}</span>

                            @if (isset($actionMeta[$log->action]))
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $actionMeta[$log->action][1] }}">{{ $actionMeta[$log->action][0] }}</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $log->subjectLabel() }} <span class="text-gray-400">#{{ $log->subject_id }}</span></span>
                            @else
                                <span class="text-gray-600 dark:text-gray-300">{{ $log->description ?? $log->action }}</span>
                            @endif

                            <div class="ml-auto flex flex-wrap items-center gap-x-3 gap-y-1">
                                @if ($log->undone_at)
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">Restauré</span>
                                @endif
                                @if ($hasDetail)
                                    <button type="button" @click="open = ! open" class="text-xs text-gray-400 hover:text-brand-600">Détails</button>
                                @endif
                                @if ($canView)
                                    <a href="{{ $log->subjectUrl() }}" class="text-xs font-medium text-brand-600 hover:underline">Voir →</a>
                                @endif
                                @if ($log->isRestorable() && \Illuminate\Support\Facades\Route::has('logs.restore') && auth()->user()->can(\App\Support\Permissions::AUDIT_RESTORE))
                                    <form action="{{ route('logs.restore', $log) }}" method="POST" onsubmit="return confirm('Restaurer cet élément supprimé ?')">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-green-600 hover:underline">Annuler</button>
                                    </form>
                                @endif
                                <span class="text-xs text-gray-400">{{ $log->ip_address }}</span>
                            </div>
                        </div>

                        @if ($hasDetail)
                            <div x-show="open" x-cloak class="mt-2 rounded-lg bg-gray-50 p-3 text-xs dark:bg-gray-800/50">
                                @if ($isUpdate)
                                    <div class="space-y-1">
                                        @foreach ($log->changes['new'] as $field => $newVal)
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="min-w-32 font-medium text-gray-500">{{ $field }}</span>
                                                <span class="text-red-500 line-through">{{ $fmt($log->changes['old'][$field] ?? null) }}</span>
                                                <span class="text-gray-400">→</span>
                                                <span class="text-green-600 dark:text-green-400">{{ $fmt($newVal) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{-- Full snapshot for a creation, deletion or restoration --}}
                                    <dl class="grid grid-cols-1 gap-x-6 gap-y-1 sm:grid-cols-2">
                                        @foreach ($snapshot as $field => $value)
                                            <div class="flex flex-wrap gap-2">
                                                <dt class="font-medium text-gray-500">{{ $field }}</dt>
                                                <dd class="text-gray-700 dark:text-gray-300">{{ $fmt($value) }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <x-empty-state icon="list" title="Aucune activité" />
                @endforelse
            </div>
        </x-card>
    </div>
@endsection
