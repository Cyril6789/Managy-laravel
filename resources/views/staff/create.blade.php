@extends('layouts.app')
@section('title', 'Nouveau technicien')

@section('content')
    <x-page-header title="Nouveau technicien" />
    <form action="{{ route('staff.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('staff._form')
        <div class="flex justify-end gap-2">
            <x-button variant="secondary" :href="route('staff.index')">Annuler</x-button>
            <x-button type="submit">Créer</x-button>
        </div>
    </form>
@endsection
