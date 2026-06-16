@extends('layouts.app')
@section('title', 'Nouvel automatisme')

@section('content')
    <x-page-header title="Nouvel automatisme" />
    <form action="{{ route('automatismes.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('automatismes._form')
        <div class="flex justify-end gap-2">
            <x-button variant="secondary" :href="route('automatismes.index')">Annuler</x-button>
            <x-button type="submit">Créer</x-button>
        </div>
    </form>
@endsection
