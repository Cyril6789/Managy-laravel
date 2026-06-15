@php $val = fn ($field, $default = null) => old($field, data_get($intervention, $field, $default)); @endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <x-card title="Client & matériel">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-field label="Client" name="client_id" required class="md:col-span-2">
                    <x-select name="client_id">
                        <option value="">— Sélectionner —</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}" @selected($val('client_id', request('client_id'))==$c->id)>{{ $c->nomComplet() }}</option>
                        @endforeach
                    </x-select>
                </x-field>
                <x-field label="Type de matériel" name="materiel_id">
                    <x-select name="materiel_id">
                        <option value="">—</option>
                        @foreach ($materiels as $m)<option value="{{ $m->id }}" @selected($val('materiel_id')==$m->id)>{{ $m->nom }}</option>@endforeach
                    </x-select>
                </x-field>
                <x-field label="Système d'exploitation" name="systeme_exploitation_id">
                    <x-select name="systeme_exploitation_id">
                        <option value="">—</option>
                        @foreach ($systemes as $s)<option value="{{ $s->id }}" @selected($val('systeme_exploitation_id')==$s->id)>{{ $s->nom }}</option>@endforeach
                    </x-select>
                </x-field>
                <x-field label="Antivirus" name="antivirus_id">
                    <x-select name="antivirus_id">
                        <option value="">—</option>
                        @foreach ($antivirus as $a)<option value="{{ $a->id }}" @selected($val('antivirus_id')==$a->id)>{{ $a->nom }}</option>@endforeach
                    </x-select>
                </x-field>
                <x-field label="Mot de passe / accès" name="mdp" hint="Visible uniquement en interne.">
                    <x-input name="mdp" value="{{ $val('mdp') }}" />
                </x-field>
                <x-field label="Matériel déposé" name="materiel_depose" class="md:col-span-2">
                    <x-textarea name="materiel_depose" rows="2">{{ $val('materiel_depose') }}</x-textarea>
                </x-field>
                <x-field label="Panne signalée" name="panne" class="md:col-span-2">
                    <x-textarea name="panne" rows="3">{{ $val('panne') }}</x-textarea>
                </x-field>
                <x-field label="Note interne" name="message_interne" class="md:col-span-2">
                    <x-textarea name="message_interne" rows="2">{{ $val('message_interne') }}</x-textarea>
                </x-field>
            </div>
        </x-card>
    </div>

    <div class="space-y-5">
        <x-card title="Suivi">
            <div class="space-y-5">
                <x-field label="Statut" name="statut_id">
                    <x-select name="statut_id">
                        @foreach ($statuts as $s)<option value="{{ $s->id }}" @selected($val('statut_id')==$s->id)>{{ $s->nom }}</option>@endforeach
                    </x-select>
                </x-field>
                <x-field label="Lieu" name="type_lieu" required>
                    <x-select name="type_lieu">
                        <option value="atelier" @selected($val('type_lieu')==='atelier')>Atelier</option>
                        <option value="domicile" @selected($val('type_lieu')==='domicile')>Domicile / sur site</option>
                    </x-select>
                </x-field>
                <div class="grid grid-cols-2 gap-3">
                    <x-field label="RDV début" name="rdv_debut">
                        <x-input name="rdv_debut" type="datetime-local" value="{{ $val('rdv_debut') ? \Illuminate\Support\Carbon::parse($val('rdv_debut'))->format('Y-m-d\TH:i') : '' }}" />
                    </x-field>
                    <x-field label="RDV fin" name="rdv_fin">
                        <x-input name="rdv_fin" type="datetime-local" value="{{ $val('rdv_fin') ? \Illuminate\Support\Carbon::parse($val('rdv_fin'))->format('Y-m-d\TH:i') : '' }}" />
                    </x-field>
                </div>
                <x-field label="Tarif estimatif (€)" name="tarif_estimatif">
                    <x-input name="tarif_estimatif" type="number" step="0.01" value="{{ $val('tarif_estimatif') }}" />
                </x-field>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="urgente" value="0">
                    <input type="checkbox" name="urgente" value="1" @checked($val('urgente')) class="rounded border-gray-300 text-brand-600"> Urgente
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="garantie" value="0">
                    <input type="checkbox" name="garantie" value="1" @checked($val('garantie')) class="rounded border-gray-300 text-brand-600"> Sous garantie
                </label>
            </div>
        </x-card>
    </div>
</div>
