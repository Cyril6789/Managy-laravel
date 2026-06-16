<div x-data="{ dirty: false }"
     @input="dirty = true"
     @report-saved.window="dirty = false"
     x-init="window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } })"
     class="space-y-4">

    <x-field>
        <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Diagnostic / rapport technique</label>
            @if ($rapportTypes->isNotEmpty())
                <select @change="$wire.applyModele('diagnostic', $event.target.value, 'replace'); $event.target.selectedIndex = 0"
                        class="rounded-lg border-gray-300 py-1 text-xs dark:border-gray-700 dark:bg-gray-800">
                    <option value="">Diag récurrent…</option>
                    @foreach ($rapportTypes as $t)<option value="{{ $t->texte }}">{{ \Illuminate\Support\Str::limit($t->titre, 40) }}</option>@endforeach
                </select>
            @endif
        </div>
        <x-textarea wire:model.blur="diagnostic" rows="4" />
    </x-field>

    <x-field>
        <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Message au client</label>
            @if ($commentaireTypes->isNotEmpty())
                <select @change="$wire.applyModele('message_client', $event.target.value, 'replace'); $event.target.selectedIndex = 0"
                        class="rounded-lg border-gray-300 py-1 text-xs dark:border-gray-700 dark:bg-gray-800">
                    <option value="">Modèle…</option>
                    @foreach ($commentaireTypes as $t)<option value="{{ $t->texte }}">{{ \Illuminate\Support\Str::limit($t->titre, 40) }}</option>@endforeach
                </select>
            @endif
        </div>
        <x-textarea wire:model.blur="message_client" rows="2" />
    </x-field>

    <x-field>
        <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Matériel ajouté / remplacé</label>
            @if ($materielAjouteTypes->isNotEmpty())
                <select @change="$wire.applyModele('materiel_ajoute', $event.target.value, 'append'); $event.target.selectedIndex = 0"
                        class="rounded-lg border-gray-300 py-1 text-xs dark:border-gray-700 dark:bg-gray-800">
                    <option value="">Ajouter…</option>
                    @foreach ($materielAjouteTypes as $t)<option value="{{ $t->texte }}">{{ \Illuminate\Support\Str::limit($t->nom, 40) }}</option>@endforeach
                </select>
            @endif
        </div>
        <x-textarea wire:model.blur="materiel_ajoute" rows="2" />
    </x-field>

    <div class="grid grid-cols-2 gap-3">
        <x-field label="Tarif estimatif (€)"><x-input wire:model.blur="tarif_estimatif" type="number" step="0.01" /></x-field>
        <x-field label="Accès / mot de passe"><x-input wire:model.blur="mdp" /></x-field>
    </div>

    <x-field label="Note interne"><x-textarea wire:model.blur="message_interne" rows="2" /></x-field>

    @error('tarif_estimatif')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

    <div class="flex items-center justify-between gap-2 text-xs">
        <span>
            <span wire:loading wire:target="save,diagnostic,message_client,materiel_ajoute,message_interne,mdp,tarif_estimatif" class="text-amber-600">Enregistrement…</span>
            <span wire:loading.remove class="text-green-600" x-show="!dirty" x-cloak>✓ Enregistré automatiquement</span>
            <span x-show="dirty" x-cloak class="text-amber-600">Modifications non enregistrées</span>
        </span>
        <x-button type="button" wire:click="save">Enregistrer</x-button>
    </div>
</div>
