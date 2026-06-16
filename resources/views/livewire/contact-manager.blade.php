<div>
    <x-card :padding="false">
        <x-slot:title>Contacts ({{ $contacts->count() }})</x-slot:title>
        <x-slot:actions>
            <button wire:click="openCreate" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800" title="Ajouter un contact">
                <x-icon name="plus" class="h-4 w-4" />
            </button>
        </x-slot:actions>

        <ul class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($contacts as $contact)
                <li wire:key="contact-{{ $contact->id }}" class="flex items-center justify-between gap-2 px-5 py-3">
                    <div class="min-w-0">
                        <a href="{{ route('clients.show', $contact) }}" class="font-medium text-brand-600 hover:underline">{{ $contact->nomComplet() }}</a>
                        <p class="truncate text-xs text-gray-400">{{ $contact->email ?: $contact->telephone_mobile ?: '—' }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <button wire:click="openEdit({{ $contact->id }})" class="rounded p-1 text-gray-400 hover:text-brand-600" title="Modifier">
                            <x-icon name="cog" class="h-4 w-4" />
                        </button>
                        <button wire:click="delete({{ $contact->id }})" wire:confirm="Supprimer ce contact ?" class="rounded p-1 text-gray-300 hover:text-red-600" title="Supprimer">&times;</button>
                    </div>
                </li>
            @empty
                <li class="px-5 py-6 text-center text-sm text-gray-400">Aucun contact. Ajoutez les salariés de cette entreprise.</li>
            @endforelse
        </ul>
        @error('delete')<p class="px-5 py-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </x-card>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4 py-10" wire:key="contact-manager-modal">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold">{{ $mode === 'edit' ? 'Modifier le contact' : 'Nouveau contact' }}</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-field label="Civilité">
                        <x-select wire:model="form.civilite">
                            @foreach (['' => '—', 'M.' => 'M.', 'Mme' => 'Mme'] as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                        </x-select>
                    </x-field>
                    <div></div>
                    <x-field label="Nom" :required="true">
                        <x-input wire:model="form.nom" />
                        @error('form.nom')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </x-field>
                    <x-field label="Prénom"><x-input wire:model="form.prenom" /></x-field>
                    <x-field label="E-mail" class="sm:col-span-2">
                        <x-input wire:model="form.email" type="email" />
                        @error('form.email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </x-field>
                    <x-field label="Téléphone mobile"><x-input wire:model="form.telephone_mobile" /></x-field>
                    <x-field label="Téléphone fixe"><x-input wire:model="form.telephone_fixe" /></x-field>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <x-button type="button" variant="secondary" wire:click="$set('showModal', false)">Annuler</x-button>
                    <x-button type="button" wire:click="save">{{ $mode === 'edit' ? 'Enregistrer' : 'Créer le contact' }}</x-button>
                </div>
            </div>
        </div>
    @endif
</div>
