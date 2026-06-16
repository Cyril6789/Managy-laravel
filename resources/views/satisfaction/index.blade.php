@extends('layouts.app')
@section('title', 'Satisfaction')

@section('content')
    <x-page-header title="Satisfaction client" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-card>
            <p class="text-xs text-gray-500">Note moyenne</p>
            <p class="mt-1 text-3xl font-bold">{{ $moyenne ? number_format($moyenne, 1) : '—' }}<span class="text-base text-gray-400"> / 5</span></p>
        </x-card>
        <x-card>
            <p class="text-xs text-gray-500">Réponses</p>
            <p class="mt-1 text-3xl font-bold">{{ $total }}</p>
        </x-card>
        <x-card>
            <p class="mb-2 text-xs text-gray-500">Répartition</p>
            @for ($n = 5; $n >= 1; $n--)
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-3">{{ $n }}</span>
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-full bg-amber-400" style="width: {{ $total ? round(($repartition[$n] ?? 0) / $total * 100) : 0 }}%;"></div>
                    </div>
                    <span class="w-6 text-right text-gray-400">{{ $repartition[$n] ?? 0 }}</span>
                </div>
            @endfor
        </x-card>
    </div>

    <x-card class="mt-6" :padding="false">
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($reponses as $r)
                <div class="px-5 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-amber-400">{{ str_repeat('★', (int) $r->note) }}<span class="text-gray-300 dark:text-gray-600">{{ str_repeat('★', 5 - (int) $r->note) }}</span></span>
                            <span class="text-sm font-medium">{{ $r->client?->nomComplet() }}</span>
                        </div>
                        <span class="text-xs text-gray-400">{{ $r->submitted_at?->format('d/m/Y') }}</span>
                    </div>
                    @if ($r->commentaire)<p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $r->commentaire }}</p>@endif
                    @if ($r->intervention)<a href="{{ route('interventions.show', $r->intervention) }}" class="text-xs text-brand-600 hover:underline">{{ $r->intervention->reference }}</a>@endif
                </div>
            @empty
                <x-empty-state icon="star" title="Aucune réponse" message="Les avis clients apparaîtront ici." />
            @endforelse
        </div>
        @if ($reponses->hasPages())<div class="border-t border-gray-100 p-4 dark:border-gray-800">{{ $reponses->links() }}</div>@endif
    </x-card>
@endsection
