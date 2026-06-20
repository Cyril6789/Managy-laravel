<div class="grid grid-cols-1 gap-5 md:grid-cols-2" x-data="{ type: '{{ old('type', $client->type ?: 'particulier') }}' }"
     @address-picked="
        const d = $event.detail;
        const set = (id, v) => { const el = document.getElementById(id); if (el) { el.value = v; el.dispatchEvent(new Event('input', { bubbles: true })); } };
        set('adresse', d.adresse);
        set('code_postal', d.code_postal);
        set('ville', d.ville);
     ">
    <x-field label="Type" name="type" required>
        <x-select name="type" x-model="type">
            <option value="professionnel" @selected(old('type', $client->type)==='professionnel')>Professionnel</option>
            <option value="particulier" @selected(old('type', $client->type)==='particulier')>Particulier</option>
        </x-select>
    </x-field>
    <x-field label="Civilité" name="civilite">
        <x-select name="civilite">
            @foreach (['' => '—', 'M.' => 'M.', 'Mme' => 'Mme', 'Sté' => 'Société'] as $v => $l)
                <option value="{{ $v }}" @selected(old('civilite', $client->civilite)===$v)>{{ $l }}</option>
            @endforeach
        </x-select>
    </x-field>
    <x-field label="Nom / Raison sociale" name="nom" required>
        <x-input name="nom" value="{{ old('nom', $client->nom) }}" />
    </x-field>
    {{-- Une entreprise (professionnel) n'a pas de prénom. --}}
    <div x-show="type !== 'professionnel'" x-cloak>
        <x-field label="Prénom" name="prenom">
            <x-input name="prenom" value="{{ old('prenom', $client->prenom) }}" />
        </x-field>
    </div>
    <x-field label="E-mail" name="email">
        <x-input name="email" type="email" value="{{ old('email', $client->email) }}" />
    </x-field>
    {{-- Un particulier n'a pas de SIRET (réservé aux professionnels). --}}
    <div x-show="type === 'professionnel'" x-cloak>
        <x-field label="SIRET" name="siret">
            <x-input name="siret" value="{{ old('siret', $client->siret) }}" />
        </x-field>
    </div>
    <x-field label="Téléphone fixe" name="telephone_fixe">
        <x-input name="telephone_fixe" value="{{ old('telephone_fixe', $client->telephone_fixe) }}" />
    </x-field>
    <x-field label="Téléphone mobile" name="telephone_mobile">
        <x-input name="telephone_mobile" value="{{ old('telephone_mobile', $client->telephone_mobile) }}" />
    </x-field>
    <div class="md:col-span-2 rounded-lg border border-dashed border-brand-300 bg-brand-50/50 p-3 dark:border-brand-700 dark:bg-brand-600/5">
        <x-address-autocomplete />
    </div>
    <x-field label="Adresse" name="adresse" class="md:col-span-2">
        <x-input name="adresse" value="{{ old('adresse', $client->adresse) }}" />
    </x-field>
    <x-field label="Complément d'adresse" name="adresse_complement" class="md:col-span-2">
        <x-input name="adresse_complement" value="{{ old('adresse_complement', $client->adresse_complement) }}" />
    </x-field>
    <x-field label="Code postal" name="code_postal">
        <x-input name="code_postal" value="{{ old('code_postal', $client->code_postal) }}" />
    </x-field>
    <x-field label="Ville" name="ville">
        <x-input name="ville" value="{{ old('ville', $client->ville) }}" />
    </x-field>
    {{-- Sociétés rattachées : uniquement à l'édition d'un particulier existant. --}}
    @if ($client->exists && $client->type === 'particulier')
        <div x-show="type === 'particulier'" x-cloak class="md:col-span-2">
            <livewire:client-companies :contact="$client" />
        </div>
    @endif
    <x-field label="Notes internes" name="notes" class="md:col-span-2">
        <x-textarea name="notes" rows="3">{{ old('notes', $client->notes) }}</x-textarea>
    </x-field>

    @can(\App\Support\Permissions::CLIENTS_REMISES)
        <div class="md:col-span-2 mt-2 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Tarification du client</p>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="deplacement_gratuit" value="0">
                <input type="checkbox" name="deplacement_gratuit" value="1" @checked(old('deplacement_gratuit', $client->deplacement_gratuit)) class="rounded border-gray-300 text-brand-600">
                Déplacement toujours gratuit pour ce client
            </label>
            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-field label="Remise sur les prestations (%)" name="remise_prestations" hint="Appliquée automatiquement à toutes les prestations.">
                    <x-input name="remise_prestations" type="number" step="0.01" min="0" max="100" value="{{ old('remise_prestations', $client->remise_prestations) }}" />
                </x-field>
                <x-field label="Remise sur les pièces (%)" name="remise_pieces" hint="Appliquée automatiquement à toutes les pièces.">
                    <x-input name="remise_pieces" type="number" step="0.01" min="0" max="100" value="{{ old('remise_pieces', $client->remise_pieces) }}" />
                </x-field>
            </div>
        </div>
    @endcan
</div>
