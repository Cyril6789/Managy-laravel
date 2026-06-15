@extends('layouts.app')
@section('title', 'Modifier '.$intervention->reference)

@section('content')
    <x-page-header :title="'Modifier · '.$intervention->reference" />

    <form action="{{ route('interventions.update', $intervention) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        @include('interventions._form')
        <div class="flex justify-end gap-2">
            <x-button variant="secondary" :href="route('interventions.show', $intervention)">Annuler</x-button>
            <x-button type="submit">Enregistrer</x-button>
        </div>
    </form>
@endsection
