@php
    $val = fn ($field, $default = null) => old($field, data_get($intervention, $field, $default));
    $clientId = $val('client_id', request('client_id'));
    $clientLabel = $intervention->client?->nomComplet() ?? ($clientId ? optional(\App\Models\Client::find($clientId))->nomComplet() : '');
@endphp

<div x-data="interventionForm({ contextUrl: '{{ url('interventions/contexte-client') }}', clientId: '{{ $clientId }}' })"
     @client-selected.window="onClient($event.detail.id)"
     class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <x-card title="Client & matériel">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-field label="Client" name="client_id" required class="md:col-span-2">
                    <livewire:client-picker :selected="$clientId ? (int) $clientId : null" :selected-label="$clientLabel" />
                </x-field>

                {{-- Maintenance pack banner (after a client is selected) --}}
                <template x-if="maintenance && maintenance.has">
                    <div class="md:col-span-2 rounded-lg px-4 py-2.5 text-sm"
                         :class="maintenance.low
                            ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                            : 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300'">
                        <span class="font-semibold">Pack maintenance :</span>
                        <span x-text="Number(maintenance.balance).toFixed(2) + ' h restantes'"></span>
                        <span x-show="maintenance.low"> — ⚠️ solde bas (seuil <span x-text="maintenance.threshold"></span> h)</span>
                    </div>
                </template>
                <template x-if="maintenance && !maintenance.has">
                    <p class="md:col-span-2 text-xs text-gray-400">Ce client n'a pas de pack maintenance.</p>
                </template>

                <x-field label="Type de matériel" name="materiel_id">
                    <x-searchable-select name="materiel_id" :selected="$val('materiel_id')"
                        :options="$materiels->pluck('nom', 'id')" placeholder="—" />
                </x-field>
                <x-field label="Système d'exploitation" name="systeme_exploitation_id">
                    <x-searchable-select name="systeme_exploitation_id" :selected="$val('systeme_exploitation_id')"
                        :options="$systemes->pluck('nom', 'id')" placeholder="—" />
                </x-field>
                <x-field label="Antivirus" name="antivirus_id">
                    <x-searchable-select name="antivirus_id" :selected="$val('antivirus_id')"
                        :options="$antivirus->pluck('nom', 'id')" placeholder="—" />
                </x-field>
                <x-field label="Mot de passe / accès" name="mdp" hint="Visible uniquement en interne.">
                    <x-input name="mdp" value="{{ $val('mdp') }}" />
                </x-field>

                <x-field name="materiel_depose" class="md:col-span-2">
                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                        <label for="materiel_depose" class="text-sm font-medium text-gray-700 dark:text-gray-300">Matériel déposé</label>
                        <select x-show="hist.materiels.length" x-cloak
                                @change="fillTextarea('materiel_depose', $event.target.value, 'replace'); $event.target.selectedIndex = 0"
                                class="rounded-lg border-gray-300 py-1 text-xs dark:border-gray-700 dark:bg-gray-800">
                            <option value="">Déjà déposé…</option>
                            <template x-for="m in hist.materiels" :key="m"><option :value="m" x-text="m"></option></template>
                        </select>
                    </div>
                    <x-textarea name="materiel_depose" id="materiel_depose" rows="2">{{ $val('materiel_depose') }}</x-textarea>
                </x-field>

                <x-field name="panne" class="md:col-span-2">
                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                        <label for="panne" class="text-sm font-medium text-gray-700 dark:text-gray-300">Panne signalée</label>
                        <select x-show="hist.pannes.length" x-cloak
                                @change="fillTextarea('panne', $event.target.value, 'replace'); $event.target.selectedIndex = 0"
                                class="rounded-lg border-gray-300 py-1 text-xs dark:border-gray-700 dark:bg-gray-800">
                            <option value="">Panne récurrente…</option>
                            <template x-for="p in hist.pannes" :key="p"><option :value="p" x-text="p"></option></template>
                        </select>
                    </div>
                    <x-textarea name="panne" id="panne" rows="3">{{ $val('panne') }}</x-textarea>
                </x-field>

                <x-field name="message_interne" class="md:col-span-2">
                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                        <label for="message_interne" class="text-sm font-medium text-gray-700 dark:text-gray-300">Note interne</label>
                        <select x-show="hist.notes.length" x-cloak
                                @change="fillTextarea('message_interne', $event.target.value, 'replace'); $event.target.selectedIndex = 0"
                                class="rounded-lg border-gray-300 py-1 text-xs dark:border-gray-700 dark:bg-gray-800">
                            <option value="">Note récurrente…</option>
                            <template x-for="n in hist.notes" :key="n"><option :value="n" x-text="n"></option></template>
                        </select>
                    </div>
                    <x-textarea name="message_interne" id="message_interne" rows="2">{{ $val('message_interne') }}</x-textarea>
                </x-field>
            </div>
        </x-card>
    </div>

    <div class="space-y-5">
        <x-card title="Suivi">
            <div class="space-y-5">
                <x-field label="Statut" name="statut_id">
                    <x-searchable-select name="statut_id" :selected="$val('statut_id')"
                        :options="$statuts->pluck('nom', 'id')" :allow-empty="false" />
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
