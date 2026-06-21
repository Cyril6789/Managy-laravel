@php
    $val = fn ($field, $default = null) => old($field, data_get($intervention, $field, $default));
    $clientId = $val('client_id', request('client_id'));
    $clientLabel = $intervention->client?->nomComplet() ?? ($clientId ? optional(\App\Models\Client::find($clientId))->nomComplet() : '');
@endphp

<div x-data="interventionForm({ contextUrl: '{{ url('interventions/contexte-client') }}', clientId: '{{ $clientId }}', lieu: '{{ $val('type_lieu', 'atelier') }}' })"
     @client-selected.window="onClient($event.detail.id)"
     class="space-y-6">

    {{-- Type d'intervention : choix clé, fait en tout premier. --}}
    <x-card title="Type d'intervention">
        <input type="hidden" name="type_lieu" :value="lieu">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <button type="button" @click="lieu = 'atelier'"
                    :class="lieu === 'atelier' ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500 dark:bg-brand-600/10' : 'border-gray-200 dark:border-gray-700'"
                    class="flex items-start gap-3 rounded-lg border p-4 text-left transition">
                <x-icon name="wrench" class="mt-0.5 h-5 w-5 text-brand-600" />
                <span>
                    <span class="block font-medium">En atelier</span>
                    <span class="block text-xs text-gray-500">Le client dépose son matériel à la boutique.</span>
                </span>
            </button>
            <button type="button" @click="lieu = 'domicile'"
                    :class="lieu === 'domicile' ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500 dark:bg-brand-600/10' : 'border-gray-200 dark:border-gray-700'"
                    class="flex items-start gap-3 rounded-lg border p-4 text-left transition">
                <x-icon name="home" class="mt-0.5 h-5 w-5 text-brand-600" />
                <span>
                    <span class="block font-medium">Sur site / à domicile</span>
                    <span class="block text-xs text-gray-500">Le technicien se déplace chez le client.</span>
                </span>
            </button>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <x-card title="Client & matériel">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-field label="Client" name="client_id" required class="md:col-span-2">
                    <livewire:client-picker :selected="$clientId ? (int) $clientId : null" :selected-label="$clientLabel" />
                </x-field>

                <livewire:contact-picker :client-id="$clientId ? (int) $clientId : null" :contact-id="$val('contact_id') ? (int) $val('contact_id') : null" />

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

                @php $selectedTechs = $intervention->exists ? $intervention->techniciens->pluck('id')->all() : []; @endphp

                {{-- En atelier : pas de rendez-vous ni de disponibilités, juste le choix
                     du / des technicien(s). En domicile : on retrouve le RDV + l'affectation
                     selon les disponibilités. Le <fieldset> désactivé du bloc masqué exclut
                     ses champs de la soumission (un seul bloc actif à la fois). --}}
                <fieldset x-show="lieu === 'atelier'" x-cloak :disabled="lieu !== 'atelier'">
                    <span class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Technicien(s) affecté(s)</span>
                    <ul class="divide-y divide-gray-100 rounded-lg border border-gray-200 dark:divide-gray-800 dark:border-gray-700">
                        @foreach ($techniciens as $t)
                            <li class="px-3 py-2.5">
                                <label class="flex items-center gap-3">
                                    <input type="checkbox" name="technicien_ids[]" value="{{ $t->id }}"
                                           @checked(in_array($t->id, $selectedTechs, true))
                                           class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-600 text-[10px] font-semibold text-white">{{ $t->initials() }}</span>
                                    <span class="flex-1 text-sm">{{ $t->fullName() }}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </fieldset>

                <fieldset x-show="lieu === 'domicile'" x-cloak :disabled="lieu !== 'domicile'">
                    <livewire:intervention-schedule
                        mode="form"
                        :client-id="$clientId ? (int) $clientId : null"
                        :rdv-debut="$val('rdv_debut') ? \Illuminate\Support\Carbon::parse($val('rdv_debut'))->format('Y-m-d\TH:i') : null"
                        :rdv-fin="$val('rdv_fin') ? \Illuminate\Support\Carbon::parse($val('rdv_fin'))->format('Y-m-d\TH:i') : null"
                        :selected="$selectedTechs" />
                </fieldset>

                <x-field label="Tarif estimatif (€)" name="tarif_estimatif">
                    <x-input name="tarif_estimatif" type="number" step="0.01" value="{{ $val('tarif_estimatif') }}" />
                </x-field>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="urgente" value="0">
                    <input type="checkbox" name="urgente" value="1" @checked($val('urgente')) class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800"> Urgente
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="garantie" value="0">
                    <input type="checkbox" name="garantie" value="1" @checked($val('garantie')) class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800"> Sous garantie
                </label>
            </div>
        </x-card>
    </div>
    </div>
</div>
