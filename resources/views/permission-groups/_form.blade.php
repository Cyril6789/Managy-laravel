@php
    $oldMappings = old('sso_groups', $mappings->pluck('external_group')->all());
    // Alpine needs at least one empty row to render the "add" affordance.
    $mappingRows = ! empty($oldMappings) ? array_values($oldMappings) : [''];
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-1">
        <x-card title="Identité du groupe">
            <div class="space-y-4">
                <x-field label="Nom" name="name" required hint="Ex. Tech_Niv1">
                    <x-input name="name" value="{{ old('name', $group->name) }}" />
                </x-field>
                <x-field label="Description" name="description">
                    <x-input name="description" value="{{ old('description', $group->description) }}" />
                </x-field>
            </div>
        </x-card>

        <x-card title="Utilisateurs affectés">
            <p class="mb-3 text-xs text-gray-400">Affectation manuelle. Les utilisateurs ajoutés automatiquement via SSO restent membres même s'ils ne sont pas cochés ici.</p>
            <div class="max-h-72 space-y-1 overflow-y-auto pr-1">
                @forelse ($staff as $member)
                    <label class="flex items-center gap-2 rounded-lg border border-gray-100 px-3 py-2 text-sm dark:border-gray-800">
                        <input type="checkbox" name="members[]" value="{{ $member->id }}"
                               @checked(collect(old('members', $members))->map(fn ($i) => (int) $i)->contains($member->id))
                               class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                        <span class="flex-1">{{ trim(($member->prenom ?? '').' '.$member->nom) ?: $member->email }}
                            @if ($member->is_admin)<span class="text-xs text-purple-500">(gérant)</span>@endif
                        </span>
                    </label>
                @empty
                    <p class="text-sm text-gray-400">Aucun utilisateur.</p>
                @endforelse
            </div>
        </x-card>
    </div>

    <div class="space-y-6 lg:col-span-2">
        <x-card title="Permissions du groupe">
            <div class="space-y-5">
                @foreach ($catalog as $groupName => $permissions)
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $groupName }}</p>
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
        </x-card>

        <x-card title="Association SSO (facultatif)">
            <p class="mb-3 text-sm text-gray-500">
                Indiquez les groupes de sécurité de votre annuaire (SSO) qui doivent placer automatiquement l'utilisateur dans ce groupe.
                Saisissez le <strong>nom</strong> du groupe (ex. <code>sg_managy_tech_niv1</code>) ou son identifiant d'objet (GUID).
            </p>
            <div x-data="{ rows: {{ \Illuminate\Support\Js::from($mappingRows) }} }" class="space-y-2">
                <template x-for="(row, i) in rows" :key="i">
                    <div class="flex items-center gap-2">
                        <input type="text" name="sso_groups[]" x-model="rows[i]" placeholder="sg_managy_tech_niv1"
                               class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                        <button type="button" @click="rows.splice(i, 1); if (rows.length === 0) rows.push('')"
                                class="rounded-lg px-3 py-2 text-lg leading-none text-gray-400 hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-800" title="Supprimer" aria-label="Supprimer">
                            &times;
                        </button>
                    </div>
                </template>
                <button type="button" @click="rows.push('')" class="text-sm font-medium text-brand-600 hover:underline">+ Ajouter un groupe de sécurité</button>
            </div>
        </x-card>
    </div>
</div>
