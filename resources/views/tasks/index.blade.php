@extends('layouts.app')
@section('title', 'Tâches')

@section('content')
    <x-page-header title="Tâches">
        <x-slot:actions>
            @can(\App\Support\Permissions::TASKS_MANAGE)
                <div x-data="{ open: false }">
                    <x-button @click="open = true"><x-icon name="plus" class="h-4 w-4" /> Nouvelle tâche</x-button>
                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="open = false">
                        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                            <h3 class="mb-4 text-lg font-semibold">Nouvelle tâche</h3>
                            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="statut" value="a_faire">
                                <x-field label="Titre" name="titre" required><x-input name="titre" /></x-field>
                                <x-field label="Assigné à" name="user_id">
                                    <x-select name="user_id">
                                        <option value="{{ auth()->id() }}">Moi</option>
                                        @foreach ($techniciens as $t)<option value="{{ $t->id }}">{{ $t->fullName() }}</option>@endforeach
                                    </x-select>
                                </x-field>
                                <div class="grid grid-cols-2 gap-3">
                                    <x-field label="Échéance" name="echeance"><x-input name="echeance" type="date" /></x-field>
                                    <x-field label="Heures estimées" name="heures_estimees"><x-input name="heures_estimees" type="number" step="0.25" /></x-field>
                                </div>
                                <x-field label="Description" name="description"><x-textarea name="description" rows="2" /></x-field>
                                <div class="flex justify-end gap-2">
                                    <x-button type="button" variant="secondary" @click="open = false">Annuler</x-button>
                                    <x-button type="submit">Créer</x-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card :padding="false">
        <form method="GET" class="flex flex-wrap gap-2 border-b border-gray-100 p-4 dark:border-gray-800">
            <x-select name="statut" class="w-48" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <option value="a_faire" @selected(request('statut')==='a_faire')>À faire</option>
                <option value="en_cours" @selected(request('statut')==='en_cours')>En cours</option>
                <option value="terminee" @selected(request('statut')==='terminee')>Terminées</option>
            </x-select>
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" name="mine" value="1" @checked(request('mine')) onchange="this.form.submit()" class="rounded border-gray-300 text-brand-600"> Mes tâches
            </label>
        </form>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($tasks as $task)
                <div class="flex items-center gap-3 px-5 py-3">
                    <form action="{{ route('tasks.toggle', $task) }}" method="POST">@csrf
                        <button class="flex h-5 w-5 items-center justify-center rounded border {{ $task->statut === 'terminee' ? 'border-brand-600 bg-brand-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                            @if ($task->statut === 'terminee')<x-icon name="check" class="h-3.5 w-3.5" />@endif
                        </button>
                    </form>
                    <div class="min-w-0 flex-1">
                        <p class="truncate {{ $task->statut === 'terminee' ? 'text-gray-400 line-through' : 'font-medium' }}">{{ $task->titre }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $task->user?->fullName() }}
                            @if ($task->client) · {{ $task->client->nomComplet() }} @endif
                        </p>
                    </div>
                    @if ($task->echeance)
                        <span class="text-xs {{ $task->echeance->isPast() && $task->statut !== 'terminee' ? 'text-red-500' : 'text-gray-400' }}">{{ $task->echeance->format('d/m/Y') }}</span>
                    @endif
                    @can(\App\Support\Permissions::TASKS_MANAGE)
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')
                            <button class="text-gray-300 hover:text-red-600">&times;</button>
                        </form>
                    @endcan
                </div>
            @empty
                <x-empty-state icon="check" title="Aucune tâche" />
            @endforelse
        </div>
        @if ($tasks->hasPages())<div class="border-t border-gray-100 p-4 dark:border-gray-800">{{ $tasks->links() }}</div>@endif
    </x-card>
@endsection
