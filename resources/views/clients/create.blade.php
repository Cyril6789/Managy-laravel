@extends('layouts.app')
@section('title', 'Nouveau client')

@section('content')
    <x-page-header title="Nouveau client" />

    <x-card>
        <form action="{{ route('clients.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('clients._form')
            <div class="flex justify-end gap-2">
                <x-button variant="secondary" :href="route('clients.index')">Annuler</x-button>
                <x-button type="submit">Créer le client</x-button>
            </div>
        </form>
    </x-card>
@endsection
