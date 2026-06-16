@extends('layouts.public')
@section('title', 'Votre avis')

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        @if ($satisfaction->submitted_at)
            <div class="py-8 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/40">
                    <x-icon name="check" class="h-7 w-7" />
                </div>
                <h1 class="text-xl font-semibold">Merci pour votre retour !</h1>
                <p class="mt-1 text-sm text-gray-500">Votre avis a bien été enregistré.</p>
            </div>
        @else
            <h1 class="text-xl font-semibold">Votre satisfaction</h1>
            <p class="mt-1 text-sm text-gray-500">Comment évaluez-vous notre intervention {{ $satisfaction->intervention?->reference }} ?</p>

            <form action="{{ route('public.satisfaction.store', $satisfaction->token) }}" method="POST" class="mt-6 space-y-5"
                  x-data="{ note: {{ old('note', 0) }} }">
                @csrf
                <div class="flex justify-center gap-2">
                    @for ($n = 1; $n <= 5; $n++)
                        <button type="button" @click="note = {{ $n }}"
                                :class="note >= {{ $n }} ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'"
                                class="transition hover:scale-110">
                            <x-icon name="star" class="h-10 w-10" style="fill: currentColor;" stroke-width="0.5" />
                        </button>
                    @endfor
                </div>
                <input type="hidden" name="note" :value="note">
                @error('note')<p class="text-center text-sm text-red-600">{{ $message }}</p>@enderror

                <x-field label="Un commentaire ? (facultatif)" name="commentaire">
                    <x-textarea name="commentaire" rows="3">{{ old('commentaire') }}</x-textarea>
                </x-field>

                <x-button type="submit" class="w-full" x-bind:disabled="note === 0">Envoyer mon avis</x-button>
            </form>
        @endif
    </div>
@endsection
