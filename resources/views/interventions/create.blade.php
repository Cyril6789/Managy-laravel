@extends('layouts.app')
@section('title', 'Nouvelle intervention')

@section('content')
    <x-page-header title="Nouvelle intervention" />

    <form action="{{ route('interventions.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('interventions._form')
        <div class="flex justify-end gap-2">
            <x-button variant="secondary" :href="route('interventions.index')">Annuler</x-button>
            <x-button type="submit">Créer l'intervention</x-button>
        </div>
    </form>
@endsection
