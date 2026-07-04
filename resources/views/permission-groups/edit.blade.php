@extends('layouts.app')
@section('title', 'Modifier le groupe de permissions')

@section('content')
    <x-page-header title="Modifier le groupe" :subtitle="$group->name" />
    <form action="{{ route('permission-groups.update', $group) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('permission-groups._form')
        <div class="flex items-center justify-between gap-2">
            <button type="submit"
                    form="delete-group"
                    onclick="return confirm('Supprimer ce groupe de permissions ?')"
                    class="text-sm font-medium text-red-600 hover:underline">Supprimer</button>
            <div class="flex gap-2">
                <x-button variant="secondary" :href="route('permission-groups.index')">Annuler</x-button>
                <x-button type="submit">Enregistrer</x-button>
            </div>
        </div>
    </form>

    <form id="delete-group" action="{{ route('permission-groups.destroy', $group) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endsection
