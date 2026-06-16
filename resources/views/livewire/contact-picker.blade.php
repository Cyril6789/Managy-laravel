<div class="md:col-span-2">
    {{-- Always submit a contact_id (empty when no company / no contact) --}}
    <input type="hidden" name="contact_id" value="{{ $contactId }}">

    @if ($isCompany)
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Contact (destinataire des SMS / e-mails)</label>
        <div class="flex flex-wrap gap-2">
            <select wire:model.live="contactId"
                    class="min-w-48 flex-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                <option value="">— Aucun contact (facultatif) —</option>
                @foreach ($contacts as $c)
                    <option value="{{ $c['id'] }}">{{ $c['label'] }}</option>
                @endforeach
            </select>
            @if ($contactId)
                <x-button type="button" variant="secondary" wire:click="openEdit">Modifier</x-button>
            @endif
            <x-button type="button" variant="secondary" wire:click="openCreate">+ Contact</x-button>
        </div>
        <p class="mt-1 text-xs text-gray-400">L'intervention reste rattachée à la société ; les notifications partent au contact choisi.</p>

        @if ($showModal)
            <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4 py-10" wire:key="contact-modal">
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
    @endif
</div>
