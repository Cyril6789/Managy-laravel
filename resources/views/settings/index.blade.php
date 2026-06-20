@extends('layouts.app')
@section('title', 'Paramètres')

@section('content')
    <x-page-header title="Paramètres" />

    <div x-data="{ tab: 'entreprise' }">
        <div class="mb-6 flex flex-wrap gap-1 border-b border-gray-200 dark:border-gray-800">
            @foreach (['entreprise' => 'Entreprise', 'sms' => 'SMS', 'smtp' => 'E-mail (SMTP)', 'listes' => 'Listes métier', 'statuts' => 'Statuts', 'facturation' => 'Facturation', 'modeles' => 'Modèles'] as $key => $label)
                <button @click="tab='{{ $key }}'" :class="tab==='{{ $key }}' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-4 py-2 text-sm font-medium">{{ $label }}</button>
            @endforeach
        </div>

        {{-- Entreprise --}}
        <div x-show="tab==='entreprise'">
            <x-card title="Coordonnées de l'entreprise">
                <form action="{{ route('settings.company') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    @csrf @method('PUT')
                    <div class="md:col-span-2 flex flex-wrap items-center gap-4">
                        @if (!empty($settings['company_logo']))
                            <img src="{{ route('company.logo').'?v='.substr(md5($settings['company_logo']),0,8) }}" alt="logo" class="h-14 rounded border border-gray-200 bg-white p-1 dark:border-gray-700">
                        @else
                            <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-brand-600 text-2xl font-bold text-white">M</div>
                        @endif
                        <x-field label="Logo (PNG/JPG, 2 Mo max)" name="logo" class="flex-1">
                            <x-input name="logo" type="file" accept="image/*" />
                        </x-field>
                        @if (!empty($settings['company_logo']))
                            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300 text-red-600"> Supprimer le logo
                            </label>
                        @endif
                    </div>
                    <x-field label="Nom de l'entreprise" name="company_name"><x-input name="company_name" value="{{ $settings['company_name'] ?? '' }}" /></x-field>
                    <x-field label="E-mail" name="company_email"><x-input name="company_email" type="email" value="{{ $settings['company_email'] ?? '' }}" /></x-field>
                    <x-field label="Téléphone" name="company_phone"><x-input name="company_phone" value="{{ $settings['company_phone'] ?? '' }}" /></x-field>
                    <x-field label="Site web" name="company_website"><x-input name="company_website" value="{{ $settings['company_website'] ?? '' }}" /></x-field>
                    <x-field label="Adresse" name="company_address" class="md:col-span-2"><x-input name="company_address" value="{{ $settings['company_address'] ?? '' }}" /></x-field>
                    <x-field label="Code postal" name="company_postal_code"><x-input name="company_postal_code" value="{{ $settings['company_postal_code'] ?? '' }}" /></x-field>
                    <x-field label="Ville" name="company_city"><x-input name="company_city" value="{{ $settings['company_city'] ?? '' }}" /></x-field>
                    <x-field label="SIRET" name="company_siret"><x-input name="company_siret" value="{{ $settings['company_siret'] ?? '' }}" /></x-field>
                    <x-field label="N° TVA" name="company_vat"><x-input name="company_vat" value="{{ $settings['company_vat'] ?? '' }}" /></x-field>
                    <div class="md:col-span-2 flex justify-end"><x-button type="submit">Enregistrer</x-button></div>
                </form>
            </x-card>
        </div>

        {{-- SMS --}}
        <div x-show="tab==='sms'" x-cloak>
            <x-card title="Configuration SMS">
                <form action="{{ route('settings.sms') }}" method="POST" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    @csrf @method('PUT')
                    <x-field label="Fournisseur" name="sms_provider" hint="« log » écrit dans les journaux sans envoi réel.">
                        <x-select name="sms_provider">
                            @foreach (['log' => 'Journalisation (test)', 'smsmode' => 'SMSMode', 'smsfactor' => 'SMSFactor'] as $v => $l)
                                <option value="{{ $v }}" @selected(($settings['sms_provider'] ?? 'log')===$v)>{{ $l }}</option>
                            @endforeach
                        </x-select>
                    </x-field>
                    <x-field label="Expéditeur" name="sms_sender"><x-input name="sms_sender" maxlength="11" value="{{ $settings['sms_sender'] ?? '' }}" /></x-field>
                    <x-field label="Clé API" name="sms_api_key"><x-input name="sms_api_key" value="{{ $settings['sms_api_key'] ?? '' }}" /></x-field>
                    <x-field label="Signature" name="sms_signature"><x-input name="sms_signature" value="{{ $settings['sms_signature'] ?? '' }}" /></x-field>
                    <div class="md:col-span-2 flex justify-end"><x-button type="submit">Enregistrer</x-button></div>
                </form>
            </x-card>
        </div>

        {{-- SMTP / E-mail --}}
        <div x-show="tab==='smtp'" x-cloak>
            <x-card title="Serveur d'envoi des e-mails (SMTP)">
                <form action="{{ route('settings.smtp') }}" method="POST" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    @csrf @method('PUT')
                    <x-field label="Hôte" name="mail_host" hint="ex. smtp.gmail.com"><x-input name="mail_host" value="{{ $settings['mail_host'] ?? '' }}" /></x-field>
                    <x-field label="Port" name="mail_port" hint="587 (TLS) ou 465 (SSL)"><x-input name="mail_port" type="number" value="{{ $settings['mail_port'] ?? '587' }}" /></x-field>
                    <x-field label="Utilisateur (facultatif)" name="mail_username"><x-input name="mail_username" value="{{ $settings['mail_username'] ?? '' }}" autocomplete="off" /></x-field>
                    <x-field label="Mot de passe" name="mail_password" hint="Laisser vide pour conserver l'actuel."><x-input name="mail_password" type="password" autocomplete="new-password" /></x-field>
                    <x-field label="Chiffrement" name="mail_encryption">
                        <x-select name="mail_encryption">
                            @foreach (['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'Aucun'] as $v => $l)
                                <option value="{{ $v }}" @selected(($settings['mail_encryption'] ?? 'tls')===$v)>{{ $l }}</option>
                            @endforeach
                        </x-select>
                    </x-field>
                    <div></div>
                    <x-field label="Adresse expéditeur" name="mail_from_address"><x-input name="mail_from_address" type="email" value="{{ $settings['mail_from_address'] ?? '' }}" /></x-field>
                    <x-field label="Nom expéditeur" name="mail_from_name"><x-input name="mail_from_name" value="{{ $settings['mail_from_name'] ?? '' }}" /></x-field>
                    <div class="md:col-span-2 flex justify-end"><x-button type="submit">Enregistrer</x-button></div>
                </form>
                <p class="mt-3 text-xs text-gray-400">Tant qu'aucun hôte n'est renseigné, l'application utilise la configuration de votre fichier <code>.env</code>.</p>
            </x-card>
        </div>

        {{-- Listes métier (tout en Livewire, édition sans rechargement) --}}
        <div x-show="tab==='listes'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <livewire:settings.reference-list type="materiels" title="Types de matériel" />
            <livewire:settings.reference-list type="systemes" title="Systèmes d'exploitation" />
            <livewire:settings.reference-list type="antivirus" title="Antivirus" />
            <livewire:settings.reference-list type="prestations" title="Prestations" />
        </div>

        {{-- Statuts --}}
        <div x-show="tab==='statuts'" x-cloak class="space-y-6">
            <x-card title="Automatisation des statuts">
                <form action="{{ route('settings.automation') }}" method="POST" class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    @csrf @method('PUT')
                    <x-field label="Statut « en attente de réception »" name="statut_attente_id" hint="Appliqué quand une commande / sous-traitance est en cours.">
                        <x-select name="statut_attente_id">
                            <option value="">— Désactivé —</option>
                            @foreach ($statuts as $s)<option value="{{ $s->id }}" @selected(($settings['statut_attente_id'] ?? null)==$s->id)>{{ $s->nom }}</option>@endforeach
                        </x-select>
                    </x-field>
                    <x-field label="Statut « réception faite »" name="statut_pret_id" hint="Rétabli quand tout est reçu / retourné.">
                        <x-select name="statut_pret_id">
                            <option value="">— Aucun —</option>
                            @foreach ($statuts as $s)<option value="{{ $s->id }}" @selected(($settings['statut_pret_id'] ?? null)==$s->id)>{{ $s->nom }}</option>@endforeach
                        </x-select>
                    </x-field>
                    <x-field label="Statut « finalisée / terminée »" name="statut_finalise_id" hint="Appliqué quand le technicien finalise l'intervention (atelier).">
                        <x-select name="statut_finalise_id">
                            <option value="">— Automatique —</option>
                            @foreach ($statuts as $s)<option value="{{ $s->id }}" @selected(($settings['statut_finalise_id'] ?? null)==$s->id)>{{ $s->nom }}</option>@endforeach
                        </x-select>
                    </x-field>
                    <x-field label="Seuil d'alerte maintenance (h)" name="maintenance_alert_threshold">
                        <x-input name="maintenance_alert_threshold" type="number" step="0.5" min="0" value="{{ $settings['maintenance_alert_threshold'] ?? '2' }}" />
                    </x-field>
                    <div class="md:col-span-3 flex justify-end"><x-button type="submit">Enregistrer</x-button></div>
                </form>
            </x-card>

            <livewire:settings.reference-list type="statuts" title="Statuts d'intervention" />
        </div>

        {{-- Facturation / déplacement --}}
        <div x-show="tab==='facturation'" x-cloak class="space-y-6">
            <x-card title="Frais de déplacement (interventions à domicile)">
                <form action="{{ route('settings.billing') }}" method="POST" class="space-y-5"
                      x-data="{ mode: '{{ $settings['deplacement_mode'] ?? 'aucun' }}' }">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                        <x-field label="Mode de calcul" name="deplacement_mode" hint="Appliqué à la signature d'une intervention à domicile.">
                            <x-select name="deplacement_mode" x-model="mode">
                                @foreach (['aucun' => 'Aucun (gratuit)', 'forfait' => 'Forfait fixe', 'km' => 'Au kilomètre'] as $v => $l)
                                    <option value="{{ $v }}" @selected(($settings['deplacement_mode'] ?? 'aucun')===$v)>{{ $l }}</option>
                                @endforeach
                            </x-select>
                        </x-field>
                        <x-field label="Forfait (€)" name="deplacement_forfait" x-show="mode==='forfait'" x-cloak>
                            <x-input name="deplacement_forfait" type="number" step="0.01" min="0" value="{{ $settings['deplacement_forfait'] ?? '' }}" />
                        </x-field>
                        <x-field label="Prix au km (€)" name="deplacement_prix_km" x-show="mode==='km'" x-cloak>
                            <x-input name="deplacement_prix_km" type="number" step="0.01" min="0" value="{{ $settings['deplacement_prix_km'] ?? '' }}" />
                        </x-field>
                    </div>
                    <x-field label="Villes gratuites" name="deplacement_villes_gratuites"
                             hint="Une ville par ligne (ou séparées par des virgules). Le déplacement est offert quand la ville du client correspond.">
                        <x-textarea name="deplacement_villes_gratuites" rows="3" placeholder="Ex.&#10;Lyon&#10;Villeurbanne">{{ $settings['deplacement_villes_gratuites'] ?? '' }}</x-textarea>
                    </x-field>
                    <div class="flex justify-end"><x-button type="submit">Enregistrer</x-button></div>
                </form>
            </x-card>
        </div>

        {{-- Modèles (rapports / commentaires) — Livewire --}}
        <div x-show="tab==='modeles'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <livewire:settings.reference-list type="rapport-types" title="Rapports types" />
            <livewire:settings.reference-list type="commentaire-types" title="Commentaires types" />
        </div>
    </div>
@endsection
