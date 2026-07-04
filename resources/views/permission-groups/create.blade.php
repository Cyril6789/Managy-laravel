@extends('layouts.app')
@section('title', 'Nouveau groupe de permissions')

@section('content')
    <x-page-header title="Nouveau groupe de permissions" />
    <form action="{{ route('permission-groups.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('permission-groups._form')
        <div class="flex justify-end gap-2">
            <x-button variant="secondary" :href="route('permission-groups.index')">Annuler</x-button>
            <x-button type="submit">Créer</x-button>
        </div>
    </form>
@endsection
