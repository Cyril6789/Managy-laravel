@php
    $offsetMin = (int) old('offset_minutes', $automatisme->offset_minutes);
    $sens = $offsetMin < 0 ? 'avant' : 'apres';
    $absMin = abs($offsetMin);
    [$valeur, $unite] = $absMin % 1440 === 0 && $absMin !== 0 ? [$absMin / 1440, 'jours']
        : ($absMin % 60 === 0 && $absMin !== 0 ? [$absMin / 60, 'heures'] : [$absMin, 'min']);
@endphp

<x-card x-data="{ evt: '{{ old('evenement', $automatisme->evenement ?: 'intervention_creee') }}' }">
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <x-field label="Libellé" name="libelle" required class="md:col-span-2">
            <x-input name="libelle" value="{{ old('libelle', $automatisme->libelle) }}" />
        </x-field>
        <x-field label="Événement déclencheur" name="evenement" required>
            <x-select name="evenement" x-model="evt">
                @foreach ($events as $key => $label)<option value="{{ $key }}" @selected(old('evenement', $automatisme->evenement)===$key)>{{ $label }}</option>@endforeach
            </x-select>
        </x-field>
        <x-field label="Canal" name="canal" required>
            <x-select name="canal">
                <option value="sms" @selected(old('canal', $automatisme->canal)==='sms')>SMS</option>
                <option value="email" @selected(old('canal', $automatisme->canal)==='email')>E-mail</option>
            </x-select>
        </x-field>

        {{-- Scheduling (only for the appointment event) --}}
        <div x-show="evt === 'rendez_vous'" x-cloak class="md:col-span-2 rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
            <p class="mb-3 text-sm font-medium">Déclenchement par rapport au rendez-vous</p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <x-field label="Quand">
                    <x-select name="offset_sens">
                        <option value="avant" @selected($sens==='avant')>Avant</option>
                        <option value="apres" @selected($sens==='apres')>Après</option>
                    </x-select>
                </x-field>
                <x-field label="Valeur">
                    <x-input name="offset_valeur" type="number" min="0" value="{{ old('offset_valeur', (int) $valeur) }}" />
                </x-field>
                <x-field label="Unité">
                    <x-select name="offset_unite">
                        @foreach (['min' => 'Minutes', 'heures' => 'Heures', 'jours' => 'Jours'] as $v => $l)
                            <option value="{{ $v }}" @selected(old('offset_unite', $unite)===$v)>{{ $l }}</option>
                        @endforeach
                    </x-select>
                </x-field>
                <x-field label="Lieu concerné">
                    <x-select name="type_lieu">
                        <option value="">Tous</option>
                        <option value="atelier" @selected(old('type_lieu', $automatisme->type_lieu)==='atelier')>Atelier</option>
                        <option value="domicile" @selected(old('type_lieu', $automatisme->type_lieu)==='domicile')>Domicile</option>
                    </x-select>
                </x-field>
            </div>
            <p class="mt-2 text-xs text-gray-400">Ex. « 1 jour avant » (rappel), « 15 min avant » (le technicien arrive), « 3 h après » (satisfaction). Nécessite le cron <code>managy:run-automatismes</code>.</p>
        </div>

        <x-field label="Condition de statut (facultatif)" name="statut_id">
            <x-select name="statut_id">
                <option value="">Tous les statuts</option>
                @foreach ($statuts as $s)<option value="{{ $s->id }}" @selected(old('statut_id', $automatisme->statut_id)==$s->id)>{{ $s->nom }}</option>@endforeach
            </x-select>
        </x-field>
        <x-field label="Sujet (e-mail)" name="sujet">
            <x-input name="sujet" value="{{ old('sujet', $automatisme->sujet) }}" />
        </x-field>
        <x-field label="Message" name="modele" required class="md:col-span-2"
                 hint="Variables : {reference} {client} {statut} {lien} {entreprise} {date_rdv} {heure_rdv}">
            <x-textarea name="modele" rows="4">{{ old('modele', $automatisme->modele) }}</x-textarea>
        </x-field>
        <label class="flex items-center gap-2 text-sm">
            <input type="hidden" name="actif" value="0">
            <input type="checkbox" name="actif" value="1" @checked(old('actif', $automatisme->actif)) class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800"> Actif
        </label>
    </div>
</x-card>
