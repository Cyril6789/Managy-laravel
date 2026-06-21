@extends('layouts.public')
@section('title', 'Suivi intervention '.$intervention->reference)

@php $i = $intervention; @endphp

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-sm text-gray-500">Suivi de votre intervention</p>
                <h1 class="text-2xl font-bold">N° {{ $i->reference }}</h1>
            </div>
            @if ($i->estCloturee())
                <x-badge color="#16a34a">Terminée</x-badge>
            @elseif ($i->statut)
                <x-badge :color="$i->statut->couleur">{{ $i->statut->nom }}</x-badge>
            @endif
        </div>

        {{-- Timeline --}}
        <div class="mt-6 grid grid-cols-3 gap-2 text-center text-xs">
            @php
                $etapes = [
                    ['Reçue', $i->opened_at !== null],
                    ['En cours', ! $i->estCloturee() || $i->closed_at],
                    ['Terminée', $i->estCloturee()],
                ];
            @endphp
            @foreach ($etapes as $idx => [$label, $done])
                <div>
                    <div class="mx-auto flex h-9 w-9 items-center justify-center rounded-full {{ $done ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-400 dark:bg-gray-700' }}">
                        @if ($done)<x-icon name="check" class="h-5 w-5" />@else {{ $idx + 1 }} @endif
                    </div>
                    <p class="mt-1 font-medium {{ $done ? 'text-gray-700 dark:text-gray-200' : 'text-gray-400' }}">{{ $label }}</p>
                </div>
            @endforeach
        </div>

        {{-- Pending alerts --}}
        @if (! $i->estCloturee() && ($commandeEnAttente || $sstEnAttente))
            <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-900/20 dark:text-amber-300">
                Votre intervention est en attente de réception de pièces ou de retour d'un prestataire.
            </div>
        @endif

        <dl class="mt-6 space-y-3 text-sm">
            <div class="flex justify-between gap-3 border-t border-gray-100 pt-3 dark:border-gray-800">
                <dt class="text-gray-500">Client</dt><dd class="font-medium">{{ $i->client?->nomComplet() }}</dd>
            </div>
            @if ($i->materiel)
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Matériel</dt><dd>{{ $i->materiel->nom }}</dd></div>
            @endif
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Reçue le</dt><dd>{{ $i->opened_at?->format('d/m/Y') }}</dd></div>
            @if ($i->estCloturee())
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Terminée le</dt><dd>{{ $i->closed_at?->format('d/m/Y') }}</dd></div>
            @endif
        </dl>

        @if ($i->panne)
            <div class="mt-5">
                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-400">Demande</p>
                <p class="whitespace-pre-line text-gray-700 dark:text-gray-300">{{ $i->panne }}</p>
            </div>
        @endif

        @if ($i->message_client)
            <div class="mt-5 rounded-lg bg-brand-50 p-4 text-sm text-brand-900 dark:bg-brand-600/10 dark:text-brand-200">
                <p class="mb-1 font-medium">Message de notre équipe</p>
                <p class="whitespace-pre-line">{{ $i->message_client }}</p>
            </div>
        @endif

        @if ($i->prestations->isNotEmpty() && $i->estCloturee())
            <div class="mt-5">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-400">Prestations réalisées</p>
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @foreach ($i->prestations as $p)
                        <li class="flex justify-between py-1.5"><span>{{ $p->designation }}</span><span class="text-gray-400">{{ rtrim(rtrim(number_format($p->duree, 2), '0'), '.') }} h</span></li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Photos partagées par l'atelier (les photos « privées » ne sont jamais exposées) --}}
        @if ($i->photos()->where('prive', false)->exists())
            <div class="mt-5">
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-400">Photos</p>
                <livewire:intervention-photos :token="$intervention->public_token" :key="'photos-pub-'.$i->id" />
            </div>
        @endif
    </div>

    @if ($i->estCloturee())
        {{-- Closed: invite the customer to rate the intervention (chat is hidden). --}}
        @if ($satisfaction && ! $satisfaction->submitted_at)
            <div class="mt-6 rounded-2xl border border-brand-200 bg-brand-50 p-6 text-center shadow-sm dark:border-brand-800 dark:bg-brand-600/10">
                <h2 class="text-lg font-semibold text-brand-900 dark:text-brand-100">Votre intervention est terminée 🎉</h2>
                <p class="mt-1 text-sm text-brand-800 dark:text-brand-200">Votre avis nous aide à nous améliorer. Cela ne prend qu'une minute.</p>
                <a href="{{ route('public.satisfaction', $satisfaction->token) }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                    <x-icon name="star" class="h-4 w-4" />
                    Donner mon avis
                </a>
            </div>
        @elseif ($satisfaction && $satisfaction->submitted_at)
            <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-600 dark:text-gray-300">Merci d'avoir partagé votre avis sur cette intervention. 🙏</p>
            </div>
        @endif
    @else
        {{-- Two-way chat with the workshop (only while the job is ongoing) --}}
        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-1 text-lg font-semibold">Une question ? Échangez avec nous</h2>
            <p class="mb-4 text-sm text-gray-500">Vos messages arrivent directement à l'équipe en charge de votre intervention.</p>
            <livewire:client-chat :token="$intervention->public_token" />
        </div>
    @endif
@endsection
