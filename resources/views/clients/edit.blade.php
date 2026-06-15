@extends('layouts.app')
@section('title', 'Modifier le client')

@section('content')
    <x-page-header :title="'Modifier · '.$client->nomComplet()" />

    <x-card>
        <form action="{{ route('clients.update', $client) }}" method="POST" class="space-y-6">
            @csrf @method('PUT')
            @include('clients._form')
            <div class="flex justify-end gap-2">
                <x-button variant="secondary" :href="route('clients.show', $client)">Annuler</x-button>
                <x-button type="submit">Enregistrer</x-button>
            </div>
        </form>
    </x-card>
@endsection
