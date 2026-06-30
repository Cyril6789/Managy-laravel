<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-1">
        <x-card title="Identité">
            <div class="space-y-4">
                <x-field label="Prénom" name="prenom"><x-input name="prenom" value="{{ old('prenom', $user->prenom) }}" /></x-field>
                <x-field label="Nom" name="nom" required><x-input name="nom" value="{{ old('nom', $user->nom) }}" /></x-field>
                <x-field label="E-mail (identifiant de connexion)" name="email" required><x-input name="email" type="email" value="{{ old('email', $user->email) }}" /></x-field>
                <x-field label="Pseudo (facultatif)" name="pseudo"><x-input name="pseudo" value="{{ old('pseudo', $user->pseudo) }}" /></x-field>
                <x-field label="Téléphone" name="telephone"><x-input name="telephone" value="{{ old('telephone', $user->telephone) }}" /></x-field>
                <x-field label="{{ $user->exists ? 'Nouveau mot de passe' : 'Mot de passe' }}" name="password" :required="!$user->exists" hint="{{ $user->exists ? 'Laisser vide pour conserver.' : '' }}">
                    <x-input name="password" type="password" autocomplete="new-password" />
                </x-field>
                <x-field label="Confirmer le mot de passe" name="password_confirmation">
                    <x-input name="password_confirmation" type="password" autocomplete="new-password" />
                </x-field>
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800"> Compte actif
                </label>
            </div>
        </x-card>
    </div>

    <div class="lg:col-span-2">
        <x-card title="Droits d'accès" x-data="{ admin: {{ old('is_admin', $user->is_admin) ? 'true' : 'false' }} }">
            <label class="mb-4 flex items-center gap-2 rounded-lg bg-purple-50 p-3 text-sm dark:bg-purple-900/20">
                <input type="hidden" name="is_admin" value="0">
                <input type="checkbox" name="is_admin" value="1" x-model="admin" @checked(old('is_admin', $user->is_admin)) class="rounded border-gray-300 text-purple-600 dark:border-gray-700 dark:bg-gray-800">
                <span><strong>Administrateur (gérant)</strong> — accès complet à toutes les fonctionnalités.</span>
            </label>

            <div x-show="!admin" class="space-y-5">
                @foreach ($catalog as $group => $permissions)
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $group }}</p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($permissions as $key => $label)
                                <label class="flex items-center gap-2 rounded-lg border border-gray-100 px-3 py-2 text-sm dark:border-gray-800">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                           @checked(collect(old('permissions', $granted))->contains($key))
                                           class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <p x-show="admin" x-cloak class="text-sm text-gray-500">Les administrateurs disposent de tous les droits ; la sélection détaillée est désactivée.</p>
        </x-card>
    </div>
</div>
