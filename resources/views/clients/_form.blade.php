<div class="grid grid-cols-1 gap-5 md:grid-cols-2" x-data="{ type: '{{ old('type', $client->type ?: 'particulier') }}' }">
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
    <x-field label="SIRET" name="siret">
        <x-input name="siret" value="{{ old('siret', $client->siret) }}" />
    </x-field>
    <x-field label="Téléphone fixe" name="telephone_fixe">
        <x-input name="telephone_fixe" value="{{ old('telephone_fixe', $client->telephone_fixe) }}" />
    </x-field>
    <x-field label="Téléphone mobile" name="telephone_mobile">
        <x-input name="telephone_mobile" value="{{ old('telephone_mobile', $client->telephone_mobile) }}" />
    </x-field>
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
    @isset($parents)
        <x-field label="Rattaché à (contact d'une société)" name="parent_id" class="md:col-span-2" hint="Laisser vide pour un client principal.">
            <x-select name="parent_id">
                <option value="">— Aucun (client principal) —</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected(old('parent_id', $client->parent_id)==$parent->id)>{{ $parent->nomComplet() }}</option>
                @endforeach
            </x-select>
        </x-field>
    @endisset
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
