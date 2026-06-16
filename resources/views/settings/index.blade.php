@extends('layouts.app')
@section('title', 'Paramètres')

@section('content')
    <x-page-header title="Paramètres" />

    <div x-data="{ tab: 'entreprise' }">
        <div class="mb-6 flex flex-wrap gap-1 border-b border-gray-200 dark:border-gray-800">
            @foreach (['entreprise' => 'Entreprise', 'sms' => 'SMS', 'smtp' => 'E-mail (SMTP)', 'listes' => 'Listes métier', 'statuts' => 'Statuts', 'modeles' => 'Modèles'] as $key => $label)
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

        {{-- Listes métier --}}
        <div x-show="tab==='listes'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @include('settings._reflist', ['type' => 'materiels', 'field' => 'nom', 'items' => $materiels, 'label' => 'Types de matériel', 'placeholder' => 'Ex. Ordinateur portable'])
            @include('settings._reflist', ['type' => 'systemes', 'field' => 'nom', 'items' => $systemes, 'label' => 'Systèmes d\'exploitation', 'placeholder' => 'Ex. Windows 11'])
            @include('settings._reflist', ['type' => 'antivirus', 'field' => 'nom', 'items' => $antivirus, 'label' => 'Antivirus', 'placeholder' => 'Ex. Bitdefender'])

            {{-- Prestations (with default duration) --}}
            <x-card title="Prestations" :padding="false">
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($prestations as $p)
                        <form action="{{ route('settings.reference.update', ['prestations', $p->id]) }}" method="POST" class="flex flex-wrap items-center gap-2 px-5 py-2">
                            @csrf @method('PUT')
                            <x-input name="designation" value="{{ $p->designation }}" class="w-full sm:flex-1 sm:w-auto" />
                            <x-input name="duree_defaut" type="number" step="0.25" value="{{ rtrim(rtrim(number_format($p->duree_defaut,2),'0'),'.') }}" class="w-20" title="Durée par défaut (h)" />
                            <x-button variant="secondary" type="submit">OK</x-button>
                            <button form="del-presta-{{ $p->id }}" class="px-1 text-gray-300 hover:text-red-600">&times;</button>
                        </form>
                        <form id="del-presta-{{ $p->id }}" action="{{ route('settings.reference.destroy', ['prestations', $p->id]) }}" method="POST" class="hidden" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')</form>
                    @endforeach
                </div>
                <form action="{{ route('settings.reference.store', 'prestations') }}" method="POST" class="flex flex-wrap gap-2 border-t border-gray-100 p-4 dark:border-gray-800">
                    @csrf
                    <x-input name="designation" placeholder="Désignation" class="w-full sm:flex-1 sm:w-auto" />
                    <x-input name="duree_defaut" type="number" step="0.25" placeholder="h" class="w-20" />
                    <x-button type="submit"><x-icon name="plus" class="h-4 w-4" /></x-button>
                </form>
            </x-card>
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
                    <x-field label="Seuil d'alerte maintenance (h)" name="maintenance_alert_threshold">
                        <x-input name="maintenance_alert_threshold" type="number" step="0.5" min="0" value="{{ $settings['maintenance_alert_threshold'] ?? '2' }}" />
                    </x-field>
                    <div class="md:col-span-3 flex justify-end"><x-button type="submit">Enregistrer</x-button></div>
                </form>
            </x-card>

            <x-card title="Statuts d'intervention" :padding="false">
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($statuts as $s)
                        <form action="{{ route('settings.reference.update', ['statuts', $s->id]) }}" method="POST" class="flex flex-wrap items-center gap-2 px-5 py-2">
                            @csrf @method('PUT')
                            <input type="color" name="couleur" value="{{ $s->couleur }}" class="h-9 w-10 rounded border-gray-300">
                            <x-input name="nom" value="{{ $s->nom }}" class="flex-1 min-w-40" />
                            <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="verrouille" value="1" @checked($s->verrouille) class="rounded border-gray-300 text-brand-600"> Verrouille</label>
                            <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="est_cloture" value="1" @checked($s->est_cloture) class="rounded border-gray-300 text-brand-600"> Clôture</label>
                            <x-button variant="secondary" type="submit">OK</x-button>
                            <button form="del-statut-{{ $s->id }}" class="px-1 text-gray-300 hover:text-red-600">&times;</button>
                        </form>
                        <form id="del-statut-{{ $s->id }}" action="{{ route('settings.reference.destroy', ['statuts', $s->id]) }}" method="POST" class="hidden" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')</form>
                    @endforeach
                </div>
                <form action="{{ route('settings.reference.store', 'statuts') }}" method="POST" class="flex gap-2 border-t border-gray-100 p-4 dark:border-gray-800">
                    @csrf
                    <input type="color" name="couleur" value="#64748b" class="h-9 w-10 rounded border-gray-300">
                    <x-input name="nom" placeholder="Nouveau statut" class="flex-1" />
                    <x-button type="submit"><x-icon name="plus" class="h-4 w-4" /></x-button>
                </form>
            </x-card>
        </div>

        {{-- Modèles (rapports / commentaires / matériels ajoutés) --}}
        <div x-show="tab==='modeles'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @foreach ([['rapport-types', $rapportTypes, 'Rapports types'], ['commentaire-types', $commentaireTypes, 'Commentaires types'], ['materiel-ajoute-types', $materielAjouteTypes, 'Matériels ajoutés types']] as [$type, $items, $label])
                @php $field = $type === 'materiel-ajoute-types' ? 'nom' : 'titre'; @endphp
                <x-card :title="$label" :padding="false">
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($items as $item)
                            <form action="{{ route('settings.reference.update', [$type, $item->id]) }}" method="POST" class="space-y-2 px-5 py-3">
                                @csrf @method('PUT')
                                <div class="flex items-center gap-2">
                                    <x-input name="{{ $field }}" value="{{ $item->{$field} }}" class="flex-1" />
                                    <x-button variant="secondary" type="submit">OK</x-button>
                                    <button form="del-{{ $type }}-{{ $item->id }}" class="px-1 text-gray-300 hover:text-red-600">&times;</button>
                                </div>
                                <x-textarea name="texte" rows="2">{{ $item->texte }}</x-textarea>
                            </form>
                            <form id="del-{{ $type }}-{{ $item->id }}" action="{{ route('settings.reference.destroy', [$type, $item->id]) }}" method="POST" class="hidden" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')</form>
                        @endforeach
                    </div>
                    <form action="{{ route('settings.reference.store', $type) }}" method="POST" class="space-y-2 border-t border-gray-100 p-4 dark:border-gray-800">
                        @csrf
                        <x-input name="{{ $field }}" placeholder="Titre" />
                        <x-textarea name="texte" rows="2" placeholder="Contenu du modèle…" />
                        <div class="flex justify-end"><x-button type="submit">Ajouter</x-button></div>
                    </form>
                </x-card>
            @endforeach
        </div>
    </div>
@endsection
