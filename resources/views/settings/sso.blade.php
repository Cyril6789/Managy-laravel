@extends('layouts.app')
@section('title', 'Connexion SSO')

@section('content')
    <x-page-header title="Connexion SSO" subtitle="Authentification unique Microsoft Entra ID / Google Workspace" />

    @include('partials.flash')

    <div class="mb-6 rounded-xl border border-brand-100 bg-brand-50 px-5 py-4 text-sm text-brand-800 dark:border-brand-900/40 dark:bg-brand-900/20 dark:text-brand-200">
        <p class="font-medium">Comment ça marche&nbsp;?</p>
        <ol class="mt-2 list-decimal space-y-1 pl-5">
            <li>Déclarez une application dans votre annuaire (Microsoft Entra ou Google) avec l'URL de redirection ci-dessous.</li>
            <li>Renseignez ici l'identifiant et le secret client, puis activez la connexion.</li>
            <li>Vos utilisateurs pourront alors se connecter via le bouton dédié sur la page de connexion, à partir de leur e-mail professionnel.</li>
            <li>Associez éventuellement vos groupes de sécurité à des
                <a href="{{ route('permission-groups.index') }}" class="font-medium underline">groupes de permissions</a>
                pour attribuer automatiquement les droits.</li>
        </ol>
    </div>

    {{-- Lien de connexion dédié + méthodes proposées --}}
    <form action="{{ route('settings.sso.login') }}" method="POST" class="mb-6">
        @csrf
        @method('PUT')
        <x-card title="Page de connexion dédiée">
            <p class="mb-4 text-sm text-gray-500">
                Chaque société dispose d'un lien de connexion à elle, à mettre en favori. Vos utilisateurs y retrouvent directement
                les méthodes que vous proposez.
            </p>

            <x-field label="Identifiant de la société (slug)" name="slug" hint="lettres, chiffres, tirets — 3 à 60 caractères">
                <x-input name="slug" value="{{ old('slug', $society->slug) }}" />
            </x-field>

            <p class="mt-2 text-sm text-gray-500">
                Lien&nbsp;: <span class="font-mono text-brand-600">{{ url('/login/'.$society->slug) }}</span>
            </p>

            <label class="mt-4 flex items-start gap-3 rounded-lg border border-gray-100 p-3 text-sm dark:border-gray-800">
                <input type="hidden" name="login_password_enabled" value="0">
                <input type="checkbox" name="login_password_enabled" value="1" @checked(old('login_password_enabled', $passwordEnabled))
                       class="mt-0.5 rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                <span><strong>Autoriser la connexion par mot de passe</strong> — si décochée, vos utilisateurs devront passer par le SSO.
                    Le gérant garde toujours un accès mot de passe de secours (en cas de panne du fournisseur).</span>
            </label>

            <div class="mt-4 flex justify-end">
                <x-button type="submit">Enregistrer</x-button>
            </div>
        </x-card>
    </form>

    {{-- Politique société : que faire d'un utilisateur SSO sans aucun droit ? --}}
    <form action="{{ route('settings.sso.policy') }}" method="POST" class="mb-6">
        @csrf
        @method('PUT')
        <x-card title="Utilisateur SSO sans droits">
            <p class="mb-4 text-sm text-gray-500">
                Que se passe-t-il lorsqu'un utilisateur se connecte en SSO mais n'appartient à aucun groupe de sécurité associé
                (aucune permission ne lui est donc attribuée)&nbsp;?
            </p>
            <div class="space-y-2">
                <label class="flex items-start gap-3 rounded-lg border border-gray-100 p-3 text-sm dark:border-gray-800">
                    <input type="radio" name="sso_unmapped_policy" value="read_only" @checked($unmappedPolicy !== 'deny')
                           class="mt-0.5 border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                    <span><strong>Autoriser en lecture seule</strong> — l'utilisateur se connecte mais n'accède qu'au tableau de bord
                        et à la page des disponibilités (aucune donnée modifiable, tout le reste est refusé).</span>
                </label>
                <label class="flex items-start gap-3 rounded-lg border border-gray-100 p-3 text-sm dark:border-gray-800">
                    <input type="radio" name="sso_unmapped_policy" value="deny" @checked($unmappedPolicy === 'deny')
                           class="mt-0.5 border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                    <span><strong>Bloquer la connexion</strong> — l'accès est refusé tant qu'aucun groupe/permission n'a été attribué.
                        L'utilisateur voit un message l'invitant à contacter son administrateur.</span>
                </label>
            </div>
            <div class="mt-4 flex justify-end">
                <x-button type="submit">Enregistrer</x-button>
            </div>
        </x-card>
    </form>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @foreach ($providers as $provider => $label)
            @php $connection = $connections[$provider]; @endphp
            <form action="{{ route('settings.sso.update', $provider) }}" method="POST">
                @csrf
                @method('PUT')
                <x-card :title="$label">
                    <x-slot:actions>
                        @if ($connection->enabled)
                            <x-badge color="#16a34a">Activé</x-badge>
                        @else
                            <x-badge>Désactivé</x-badge>
                        @endif
                    </x-slot:actions>

                    <div class="space-y-4">
                        <label class="flex items-center gap-2 rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800/60">
                            <input type="hidden" name="enabled" value="0">
                            <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $connection->enabled))
                                   class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                            <span><strong>Activer</strong> la connexion {{ $label }} pour ma société.</span>
                        </label>

                        <x-field label="URL de redirection (à déclarer côté fournisseur)">
                            <div class="flex items-center gap-2">
                                <code class="block w-full overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800">{{ $callbackBase }}/{{ $provider }}/callback</code>
                            </div>
                        </x-field>

                        <x-field label="Identifiant client (Client ID)" name="client_id">
                            <x-input name="client_id" value="{{ old('client_id', $connection->client_id) }}" autocomplete="off" />
                        </x-field>

                        <x-field label="Secret client (Client secret)" name="client_secret"
                                 hint="{{ filled($connection->client_secret) ? 'Un secret est enregistré — laisser vide pour le conserver.' : 'Requis pour activer la connexion.' }}">
                            <x-input name="client_secret" type="password" autocomplete="off"
                                     placeholder="{{ filled($connection->client_secret) ? '••••••••••••' : '' }}" />
                        </x-field>

                        @if ($provider === \App\Models\SsoConnection::PROVIDER_MICROSOFT)
                            <x-field label="Identifiant de l'annuaire (Tenant ID)" name="tenant_id"
                                     hint="GUID du tenant Entra, ou « common » pour multi-tenant.">
                                <x-input name="tenant_id" value="{{ old('tenant_id', $connection->tenant_id) }}" autocomplete="off" />
                            </x-field>
                        @endif

                        <x-field label="Domaines e-mail autorisés" name="allowed_domains"
                                 hint="Séparés par des virgules (ex. contoso.com, contoso.fr). Vide = aucun filtre.">
                            <x-input name="allowed_domains" value="{{ old('allowed_domains', $connection->allowed_domains) }}" placeholder="contoso.com, contoso.fr" />
                        </x-field>

                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="auto_provision_users" value="0">
                            <input type="checkbox" name="auto_provision_users" value="1"
                                   @checked(old('auto_provision_users', $connection->exists ? $connection->auto_provision_users : true))
                                   class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                            Créer automatiquement le compte à la première connexion SSO
                        </label>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <x-button type="submit">Enregistrer</x-button>
                    </div>
                </x-card>
            </form>
        @endforeach
    </div>
@endsection
