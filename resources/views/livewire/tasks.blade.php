<div>
    <x-page-header title="Tâches">
        <x-slot:actions>
            @can(\App\Support\Permissions::TASKS_MANAGE)
                <x-button wire:click="$set('showModal', true)"><x-icon name="plus" class="h-4 w-4" /> Nouvelle tâche</x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-card :padding="false">
        <div class="flex flex-wrap gap-2 border-b border-gray-100 p-4 dark:border-gray-800">
            <x-select wire:model.live="filtre" class="w-48">
                <option value="">Tous les statuts</option>
                <option value="a_faire">À faire</option>
                <option value="en_cours">En cours</option>
                <option value="terminee">Terminées</option>
            </x-select>
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" wire:model.live="mine" class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800"> Mes tâches
            </label>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($tasks as $task)
                <div wire:key="task-{{ $task->id }}" class="flex items-center gap-3 px-5 py-3">
                    @can(\App\Support\Permissions::TASKS_MANAGE)
                        <button wire:click="toggle({{ $task->id }})"
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded border {{ $task->statut === 'terminee' ? 'border-brand-600 bg-brand-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                            @if ($task->statut === 'terminee')<x-icon name="check" class="h-3.5 w-3.5" />@endif
                        </button>
                    @else
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded border {{ $task->statut === 'terminee' ? 'border-brand-600 bg-brand-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                            @if ($task->statut === 'terminee')<x-icon name="check" class="h-3.5 w-3.5" />@endif
                        </span>
                    @endcan
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
                        <button wire:click="delete({{ $task->id }})" wire:confirm="Supprimer cette tâche ?" class="text-gray-300 hover:text-red-600">&times;</button>
                    @endcan
                </div>
            @empty
                <x-empty-state icon="check" title="Aucune tâche" />
            @endforelse
        </div>

        @if ($tasks->hasPages())<div class="border-t border-gray-100 p-4 dark:border-gray-800">{{ $tasks->links() }}</div>@endif
    </x-card>

    {{-- Modale : nouvelle tâche --}}
    @can(\App\Support\Permissions::TASKS_MANAGE)
        @if ($showModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:key="task-modal" @keydown.escape.window="$wire.set('showModal', false)">
                <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">Nouvelle tâche</h3>
                    <form wire:submit="create" class="space-y-4">
                        <x-field label="Titre" required>
                            <x-input wire:model="form.titre" />
                            @error('form.titre')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </x-field>
                        <x-field label="Assigné à">
                            <x-select wire:model="form.user_id">
                                <option value="{{ auth()->id() }}">Moi</option>
                                @foreach ($techniciens as $t)<option value="{{ $t->id }}">{{ $t->fullName() }}</option>@endforeach
                            </x-select>
                        </x-field>
                        <div class="grid grid-cols-2 gap-3">
                            <x-field label="Échéance"><x-input wire:model="form.echeance" type="date" /></x-field>
                            <x-field label="Heures estimées">
                                <x-input wire:model="form.heures_estimees" type="text" inputmode="decimal" />
                                @error('form.heures_estimees')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </x-field>
                        </div>
                        <x-field label="Description"><x-textarea wire:model="form.description" rows="2" /></x-field>
                        <div class="flex justify-end gap-2">
                            <x-button type="button" variant="secondary" wire:click="$set('showModal', false)">Annuler</x-button>
                            <x-button type="submit" wire:loading.attr="disabled">Créer</x-button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan
</div>
