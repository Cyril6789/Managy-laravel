<div x-data="{ ok: false }" @settings-saved="ok = true; setTimeout(() => ok = false, 2500)">
    @php
        $saveBar = function () { return ''; };
    @endphp

    {{-- Entreprise --}}
    @if ($section === 'entreprise')
        <x-card title="Coordonnées de l'entreprise">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="md:col-span-2 flex flex-wrap items-center gap-4">
                    @if ($companyLogo)
                        <img src="{{ route('company.logo').'?v='.substr(md5($companyLogo),0,8) }}" alt="logo" class="h-14 rounded border border-gray-200 bg-white p-1 dark:border-gray-700">
                    @else
                        <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-brand-600 text-2xl font-bold text-white">M</div>
                    @endif
                    <x-field label="Logo (PNG/JPG, 2 Mo max)" class="flex-1">
                        <x-input type="file" accept="image/*" wire:model="logo" />
                        <div wire:loading wire:target="logo" class="mt-1 text-xs text-amber-600">Chargement…</div>
                        @error('logo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </x-field>
                    @if ($companyLogo)
                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <input type="checkbox" wire:model="removeLogo" class="rounded border-gray-300 text-red-600"> Supprimer le logo
                        </label>
                    @endif
                </div>
                <x-field label="Nom de l'entreprise"><x-input wire:model="data.company_name" /></x-field>
                <x-field label="E-mail"><x-input type="email" wire:model="data.company_email" />@error('data.company_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</x-field>
                <x-field label="Téléphone"><x-input wire:model="data.company_phone" /></x-field>
                <x-field label="Site web"><x-input wire:model="data.company_website" /></x-field>
                <x-field label="Adresse" class="md:col-span-2"><x-input wire:model="data.company_address" /></x-field>
                <x-field label="Code postal"><x-input wire:model="data.company_postal_code" /></x-field>
                <x-field label="Ville"><x-input wire:model="data.company_city" /></x-field>
                <x-field label="SIRET"><x-input wire:model="data.company_siret" /></x-field>
                <x-field label="N° TVA"><x-input wire:model="data.company_vat" /></x-field>
            </div>
            @include('livewire.settings._save-bar')
        </x-card>

    {{-- SMS --}}
    @elseif ($section === 'sms')
        <x-card title="Configuration SMS">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-field label="Fournisseur" hint="« Journalisation » écrit dans les journaux sans envoi réel.">
                    <x-select wire:model="data.sms_provider">
                        @foreach (['log' => 'Journalisation (test)', 'smsmode' => 'SMSMode', 'smsfactor' => 'SMSFactor'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </x-select>
                </x-field>
                <x-field label="Expéditeur"><x-input maxlength="11" wire:model="data.sms_sender" /></x-field>
                <x-field label="Clé API"><x-input wire:model="data.sms_api_key" /></x-field>
                <x-field label="Signature"><x-input wire:model="data.sms_signature" /></x-field>
            </div>
            @include('livewire.settings._save-bar')
        </x-card>

    {{-- SMTP --}}
    @elseif ($section === 'smtp')
        <x-card title="Serveur d'envoi des e-mails (SMTP)">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-field label="Hôte" hint="ex. smtp.gmail.com"><x-input wire:model="data.mail_host" /></x-field>
                <x-field label="Port" hint="587 (TLS) ou 465 (SSL)"><x-input type="number" wire:model="data.mail_port" /></x-field>
                <x-field label="Utilisateur (facultatif)"><x-input wire:model="data.mail_username" autocomplete="off" /></x-field>
                <x-field label="Mot de passe" hint="Laisser vide pour conserver l'actuel."><x-input type="password" wire:model="mailPassword" autocomplete="new-password" /></x-field>
                <x-field label="Chiffrement">
                    <x-select wire:model="data.mail_encryption">
                        @foreach (['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'Aucun'] as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                    </x-select>
                </x-field>
                <div></div>
                <x-field label="Adresse expéditeur"><x-input type="email" wire:model="data.mail_from_address" /></x-field>
                <x-field label="Nom expéditeur"><x-input wire:model="data.mail_from_name" /></x-field>
            </div>
            @include('livewire.settings._save-bar')
            <p class="mt-3 text-xs text-gray-400">Tant qu'aucun hôte n'est renseigné, l'application utilise la configuration de votre fichier <code>.env</code>.</p>
        </x-card>

    {{-- Automation --}}
    @elseif ($section === 'automation')
        <x-card title="Automatisation des statuts">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <x-field label="Statut « en attente de réception »" hint="Appliqué quand une commande / sous-traitance est en cours.">
                    <x-select wire:model="data.statut_attente_id">
                        <option value="">— Désactivé —</option>
                        @foreach ($statuts as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach
                    </x-select>
                </x-field>
                <x-field label="Statut « réception faite »" hint="Rétabli quand tout est reçu / retourné.">
                    <x-select wire:model="data.statut_pret_id">
                        <option value="">— Aucun —</option>
                        @foreach ($statuts as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach
                    </x-select>
                </x-field>
                <x-field label="Statut « finalisée / terminée »" hint="Appliqué quand le technicien finalise l'intervention (atelier).">
                    <x-select wire:model="data.statut_finalise_id">
                        <option value="">— Automatique —</option>
                        @foreach ($statuts as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach
                    </x-select>
                </x-field>
                <x-field label="Seuil d'alerte maintenance (h)">
                    <x-input type="number" step="0.5" min="0" wire:model="data.maintenance_alert_threshold" />
                </x-field>
            </div>
            @include('livewire.settings._save-bar')
        </x-card>

    {{-- Billing --}}
    @elseif ($section === 'billing')
        <x-card title="Frais de déplacement (interventions à domicile)">
            <div x-data="{ mode: @js($data['deplacement_mode'] ?? 'aucun') }" class="space-y-5">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-field label="Mode de calcul" hint="Appliqué à la signature d'une intervention à domicile.">
                        <x-select wire:model="data.deplacement_mode" x-on:change="mode = $event.target.value">
                            @foreach (['aucun' => 'Aucun (gratuit)', 'forfait' => 'Forfait fixe', 'km' => 'Au kilomètre'] as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </x-select>
                    </x-field>
                    <x-field label="Forfait (€)" x-show="mode==='forfait'" x-cloak>
                        <x-input type="number" step="0.01" min="0" wire:model="data.deplacement_forfait" />
                    </x-field>
                    <x-field label="Prix au km (€)" x-show="mode==='km'" x-cloak>
                        <x-input type="number" step="0.01" min="0" wire:model="data.deplacement_prix_km" />
                    </x-field>
                </div>
                <x-field label="Villes gratuites" hint="Une ville par ligne (ou séparées par des virgules). Le déplacement est offert quand la ville du client correspond.">
                    <x-textarea rows="3" wire:model="data.deplacement_villes_gratuites" placeholder="Ex.&#10;Lyon&#10;Villeurbanne" />
                </x-field>
            </div>
            @include('livewire.settings._save-bar')
        </x-card>
    @endif
</div>
