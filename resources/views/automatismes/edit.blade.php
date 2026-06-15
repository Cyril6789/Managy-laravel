@extends('layouts.app')
@section('title', 'Modifier automatisme')

@section('content')
    <x-page-header :title="'Modifier · '.$automatisme->libelle">
        <x-slot:actions>
            <form action="{{ route('automatismes.destroy', $automatisme) }}" method="POST" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')
                <x-button variant="danger" type="submit">Supprimer</x-button>
            </form>
        </x-slot:actions>
    </x-page-header>
    <form action="{{ route('automatismes.update', $automatisme) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        @include('automatismes._form')
        <div class="flex justify-end gap-2">
            <x-button variant="secondary" :href="route('automatismes.index')">Annuler</x-button>
            <x-button type="submit">Enregistrer</x-button>
        </div>
    </form>
@endsection
