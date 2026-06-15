<x-card>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <x-field label="Libellé" name="libelle" required class="md:col-span-2">
            <x-input name="libelle" value="{{ old('libelle', $automatisme->libelle) }}" />
        </x-field>
        <x-field label="Événement déclencheur" name="evenement" required>
            <x-select name="evenement">
                @foreach ($events as $key => $label)<option value="{{ $key }}" @selected(old('evenement', $automatisme->evenement)===$key)>{{ $label }}</option>@endforeach
            </x-select>
        </x-field>
        <x-field label="Condition de statut (facultatif)" name="statut_id">
            <x-select name="statut_id">
                <option value="">Tous les statuts</option>
                @foreach ($statuts as $s)<option value="{{ $s->id }}" @selected(old('statut_id', $automatisme->statut_id)==$s->id)>{{ $s->nom }}</option>@endforeach
            </x-select>
        </x-field>
        <x-field label="Canal" name="canal" required>
            <x-select name="canal">
                <option value="sms" @selected(old('canal', $automatisme->canal)==='sms')>SMS</option>
                <option value="email" @selected(old('canal', $automatisme->canal)==='email')>E-mail</option>
            </x-select>
        </x-field>
        <x-field label="Sujet (e-mail)" name="sujet">
            <x-input name="sujet" value="{{ old('sujet', $automatisme->sujet) }}" />
        </x-field>
        <x-field label="Message" name="modele" required class="md:col-span-2"
                 hint="Variables : {reference} {client} {statut} {lien} {entreprise}">
            <x-textarea name="modele" rows="4">{{ old('modele', $automatisme->modele) }}</x-textarea>
        </x-field>
        <label class="flex items-center gap-2 text-sm">
            <input type="hidden" name="actif" value="0">
            <input type="checkbox" name="actif" value="1" @checked(old('actif', $automatisme->actif)) class="rounded border-gray-300 text-brand-600"> Actif
        </label>
    </div>
</x-card>
