@extends('layouts.app')
@section('title', 'Modifier '.$user->fullName())

@section('content')
    <x-page-header :title="'Modifier · '.$user->fullName()">
        <x-slot:actions>
            @if ($user->id !== auth()->id())
                <form action="{{ route('staff.destroy', $user) }}" method="POST" onsubmit="return confirm('Supprimer ce technicien ?')">@csrf @method('DELETE')
                    <x-button variant="danger" type="submit">Supprimer</x-button>
                </form>
            @endif
        </x-slot:actions>
    </x-page-header>
    <form action="{{ route('staff.update', $user) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        @include('staff._form')
        <div class="flex justify-end gap-2">
            <x-button variant="secondary" :href="route('staff.index')">Annuler</x-button>
            <x-button type="submit">Enregistrer</x-button>
        </div>
    </form>
@endsection
