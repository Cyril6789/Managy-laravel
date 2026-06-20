<div x-data @click.outside="$wire.set('open', false)"
     @address-picked="$wire.fillAddress($event.detail)" class="relative">
    {{-- Value submitted by the surrounding (non-Livewire) intervention form --}}
    <input type="hidden" name="client_id" value="{{ $selectedId }}">

    <div class="flex gap-2">
        <button type="button" wire:click="$set('open', true)"
                class="flex flex-1 items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <span class="{{ $selectedLabel ? '' : 'text-gray-400' }} truncate">{{ $selectedLabel ?: 'Rechercher un client…' }}</span>
            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
        </button>
        @if ($selectedId)
            <button type="button" wire:click="openEdit"
                    class="shrink-0 rounded-lg border border-gray-300 px-3 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800" title="Modifier ce client">
                Modifier
            </button>
        @endif
    </div>

    @if ($open)
        <div class="absolute z-30 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 p-2 dark:border-gray-700">
                <input type="text" wire:model.live.debounce.300ms="query" autofocus
                       placeholder="Nom, e-mail, ville… (2 caractères min.)"
                       class="w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-900">
            </div>
            <ul class="py-1 text-sm" style="max-height:14rem;overflow-y:auto;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;touch-action:pan-y;">
                @forelse ($results as $c)
                    <li wire:key="res-{{ $c['id'] }}" wire:click="choose({{ $c['id'] }}, @js($c['label']))"
                        class="cursor-pointer px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <span class="font-medium">{{ $c['label'] }}</span>
                        @if ($c['ville'])<span class="text-xs text-gray-400"> · {{ $c['ville'] }}</span>@endif
                    </li>
                @empty
                    <li class="px-3 py-2 text-gray-400">
                        {{ strlen(trim($query)) >= 2 ? 'Aucun client trouvé.' : 'Tapez au moins 2 caractères.' }}
                    </li>
                @endforelse
            </ul>
            <div class="border-t border-gray-100 p-2 dark:border-gray-700">
                <button type="button" wire:click="openCreate"
                        class="w-full rounded-md bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">
                    + Créer « {{ trim($query) ?: 'nouveau client' }} »
                </button>
            </div>
        </div>
    @endif

    {{-- Create / edit modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4 py-10" wire:key="client-modal">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold">{{ $mode === 'edit' ? 'Modifier le client' : 'Nouveau client' }}</h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-field label="Type">
                        <x-select wire:model.live="form.type">
                            <option value="professionnel">Professionnel</option>
                            <option value="particulier">Particulier</option>
                        </x-select>
                    </x-field>
                    {{-- La civilité ne concerne que les particuliers. --}}
                    @if (($form['type'] ?? 'particulier') !== 'professionnel')
                        <x-field label="Civilité">
                            <x-select wire:model="form.civilite">
                                @foreach (['' => '—', 'M.' => 'M.', 'Mme' => 'Mme'] as $v => $l)
                                    <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </x-select>
                        </x-field>
                    @else
                        <div class="hidden sm:block"></div>
                    @endif
                    <x-field label="Nom / Raison sociale" :required="true">
                        <x-input wire:model="form.nom" />
                        @error('form.nom')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </x-field>
                    {{-- Une entreprise (professionnel) n'a pas de prénom. --}}
                    @if (($form['type'] ?? 'particulier') !== 'professionnel')
                        <x-field label="Prénom"><x-input wire:model="form.prenom" /></x-field>
                    @else
                        <div class="hidden sm:block"></div>
                    @endif
                    <x-field label="E-mail">
                        <x-input wire:model="form.email" type="email" />
                        @error('form.email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </x-field>
                    @if (($form['type'] ?? 'particulier') === 'professionnel')
                        <x-field label="SIRET"><x-input wire:model="form.siret" /></x-field>
                    @else
                        <div class="hidden sm:block"></div>
                    @endif
                    <x-field label="Téléphone fixe"><x-input wire:model="form.telephone_fixe" /></x-field>
                    <x-field label="Téléphone mobile"><x-input wire:model="form.telephone_mobile" /></x-field>
                    <div class="sm:col-span-2 rounded-lg border border-dashed border-brand-300 bg-brand-50/50 p-3 dark:border-brand-700 dark:bg-brand-600/5">
                        <x-address-autocomplete />
                    </div>
                    <x-field label="Adresse" class="sm:col-span-2"><x-input wire:model="form.adresse" /></x-field>
                    <x-field label="Code postal"><x-input wire:model="form.code_postal" /></x-field>
                    <x-field label="Ville"><x-input wire:model="form.ville" /></x-field>
                    <x-field label="Notes" class="sm:col-span-2"><x-textarea wire:model="form.notes" rows="2" /></x-field>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <x-button type="button" variant="secondary" wire:click="$set('showModal', false)">Annuler</x-button>
                    <x-button type="button" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">{{ $mode === 'edit' ? 'Enregistrer' : 'Créer le client' }}</span>
                        <span wire:loading wire:target="save">…</span>
                    </x-button>
                </div>
            </div>
        </div>
    @endif
</div>
