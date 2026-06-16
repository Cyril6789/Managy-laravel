@extends('layouts.app')
@section('title', $intervention->reference)

@php
    $i = $intervention;
    $estTech = $i->techniciens->contains(auth()->id());
    $verrou = $i->estVerrouillee();
    $peutGerer = auth()->user()->can(\App\Support\Permissions::INTERVENTIONS_MANAGE);
@endphp

@section('content')
    <x-page-header :title="'Intervention '.$i->reference" :subtitle="$i->client?->nomComplet()">
        <x-slot:actions>
            <form action="{{ route('interventions.pec', $i) }}" method="POST">@csrf
                <x-button variant="secondary" type="submit">{{ $estTech ? 'Ne plus prendre en charge' : 'Prendre en charge' }}</x-button>
            </form>
            @can(\App\Support\Permissions::INTERVENTIONS_MANAGE)
                @unless ($verrou)<x-button variant="secondary" :href="route('interventions.edit', $i)">Modifier</x-button>@endunless
            @endcan
            @if ($i->estCloturee())
                @can(\App\Support\Permissions::INTERVENTIONS_DECLOTURE)
                    <form action="{{ route('interventions.decloturer', $i) }}" method="POST">@csrf
                        <x-button variant="secondary" type="submit">Déclôturer</x-button>
                    </form>
                @endcan
            @endif
        </x-slot:actions>
    </x-page-header>

    @if ($i->estCloturee())
        <div class="mb-4 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2.5 text-sm text-green-800 dark:border-green-900 dark:bg-green-900/30 dark:text-green-300">
            <x-icon name="check" class="h-4 w-4" /> Intervention clôturée le {{ $i->closed_at?->format('d/m/Y à H:i') }}.
            @can(\App\Support\Permissions::INTERVENTIONS_FACTURATION)
                <form action="{{ route('interventions.facturation', $i) }}" method="POST" class="ml-auto">@csrf
                    <button class="font-medium underline">{{ $i->facturee ? 'Marquée facturée ✓' : 'Marquer comme facturée' }}</button>
                </form>
            @endcan
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main panel --}}
        <div class="space-y-6 lg:col-span-2" x-data="{ tab: 'details' }">
            {{-- Status quick change --}}
            <x-card>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm text-gray-500">Statut :</span>
                    <form action="{{ route('interventions.statut', $i) }}" method="POST" class="flex items-center gap-2">@csrf
                        <x-select name="statut_id" class="w-56" onchange="this.form.submit()" :disabled="! $peutGerer">
                            @foreach ($statuts as $s)<option value="{{ $s->id }}" @selected($i->statut_id==$s->id)>{{ $s->nom }}</option>@endforeach
                        </x-select>
                    </form>
                    @if ($i->urgente)
                        <x-badge color="#ef4444">Urgent</x-badge>
                    @endif
                    @if ($i->garantie)
                        <x-badge>Garantie</x-badge>
                    @endif
                    <x-badge>{{ $i->type_lieu === 'domicile' ? 'À domicile' : 'Atelier' }}</x-badge>
                </div>
            </x-card>

            {{-- Tabs --}}
            <x-card :padding="false">
                @php
                    $tabCounts = [
                        'prestations' => $i->prestations->count(),
                        'commandes' => $i->commandes->count(),
                        'sst' => $i->sousTraitances->count(),
                        'contact' => $i->clientMessages->count(),
                    ];
                @endphp
                <div class="flex gap-1 overflow-x-auto border-b border-gray-100 px-3 pt-2 dark:border-gray-800">
                    @foreach (['details' => 'Détails', 'prestations' => 'Prestations', 'commandes' => 'Commandes', 'sst' => 'Sous-traitance', 'contact' => 'Contact', 'tchat' => 'Tchat'] as $key => $label)
                        <button @click="tab = '{{ $key }}'"
                                :class="tab === '{{ $key }}' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium">{{ $label }}
                            @if (! empty($tabCounts[$key]))<span class="ml-1 text-xs text-gray-400">({{ $tabCounts[$key] }})</span>@endif
                        </button>
                    @endforeach
                </div>

                <div class="p-5">
                    {{-- Détails --}}
                    <div x-show="tab === 'details'" class="space-y-4 text-sm">
                        @foreach ([['Matériel déposé', $i->materiel_depose], ['Panne signalée', $i->panne], ['Diagnostic / rapport', $i->diagnostic], ['Matériel ajouté', $i->materiel_ajoute], ['Message au client', $i->message_client], ['Note interne', $i->message_interne]] as [$label, $value])
                            <div>
                                <p class="mb-0.5 text-xs font-medium uppercase tracking-wide text-gray-400">{{ $label }}</p>
                                <p class="whitespace-pre-line text-gray-700 dark:text-gray-300">{{ $value ?: '—' }}</p>
                            </div>
                        @endforeach
                        @if ($i->mdp)
                            <div><p class="mb-0.5 text-xs font-medium uppercase tracking-wide text-gray-400">Accès / mot de passe</p><p class="font-mono text-gray-700 dark:text-gray-300">{{ $i->mdp }}</p></div>
                        @endif
                    </div>

                    {{-- Prestations --}}
                    <div x-show="tab === 'prestations'" x-cloak class="space-y-4">
                        <table class="min-w-full text-sm">
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse ($i->prestations as $p)
                                    <tr>
                                        <td class="py-2">{{ $p->designation }}</td>
                                        <td class="py-2 text-right text-gray-500">{{ rtrim(rtrim(number_format($p->duree, 2), '0'), '.') }} h</td>
                                        <td class="py-2 pl-3 text-right">
                                            @if ($peutGerer)
                                                <form action="{{ route('prestations.destroy', $p) }}" method="POST" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')
                                                    <button class="text-gray-400 hover:text-red-600">&times;</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="py-4 text-center text-gray-400">Aucune prestation</td></tr>
                                @endforelse
                            </tbody>
                            @if ($i->prestations->count())
                                <tfoot><tr class="border-t border-gray-200 font-semibold dark:border-gray-700"><td class="py-2">Total</td><td class="py-2 text-right">{{ rtrim(rtrim(number_format($i->tempsTotal(), 2), '0'), '.') }} h</td><td></td></tr></tfoot>
                            @endif
                        </table>

                        @if ($peutGerer && ! $i->estCloturee())
                            <form action="{{ route('interventions.prestations.store', $i) }}" method="POST" class="flex flex-wrap items-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800"
                                  x-data="{ presta: '' }">
                                @csrf
                                <x-field label="Prestation" class="flex-1 min-w-48">
                                    <x-select name="prestation_id" x-model="presta"
                                              @change="const o=$event.target.selectedOptions[0]; $refs.designation.value=o.dataset.label||''; $refs.duree.value=o.dataset.duree||$refs.duree.value;">
                                        <option value="">— Saisie libre —</option>
                                        @foreach ($prestationsCatalogue as $pc)
                                            <option value="{{ $pc->id }}" data-label="{{ $pc->designation }}" data-duree="{{ $pc->duree_defaut }}">{{ $pc->designation }}</option>
                                        @endforeach
                                    </x-select>
                                </x-field>
                                <x-field label="Désignation" class="flex-1 min-w-40">
                                    <x-input name="designation" x-ref="designation" />
                                </x-field>
                                <x-field label="Durée (h)" class="w-24">
                                    <x-input name="duree" x-ref="duree" type="number" step="0.25" value="0" />
                                </x-field>
                                <x-button type="submit">Ajouter</x-button>
                            </form>
                        @endif
                    </div>

                    {{-- Commandes --}}
                    <div x-show="tab === 'commandes'" x-cloak class="space-y-4">
                        @forelse ($i->commandes as $c)
                            <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                                <div class="flex items-center justify-between">
                                    <p class="font-medium">{{ $c->fournisseur ?: 'Commande' }} {{ $c->numero_commande ? '· '.$c->numero_commande : '' }}</p>
                                    @if ($c->recue)
                                        <x-badge color="#16a34a">Reçue</x-badge>
                                    @else
                                        <form action="{{ route('commandes.update', $c) }}" method="POST">@csrf @method('PUT')
                                            <input type="hidden" name="recue" value="1">
                                            <button class="text-xs text-brand-600 hover:underline">Marquer reçue</button>
                                        </form>
                                    @endif
                                </div>
                                @if ($c->suivi_colis)<p class="text-xs text-gray-400">Suivi : {{ $c->suivi_colis }}</p>@endif
                            </div>
                        @empty
                            <p class="py-2 text-center text-sm text-gray-400">Aucune commande</p>
                        @endforelse

                        @if ($peutGerer && ! $i->estCloturee())
                            <form action="{{ route('interventions.commandes.store', $i) }}" method="POST" class="grid grid-cols-2 gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                                @csrf
                                <x-input name="fournisseur" placeholder="Fournisseur" />
                                <x-input name="numero_commande" placeholder="N° commande" />
                                <x-input name="suivi_colis" placeholder="Suivi colis" class="col-span-2" />
                                <div class="col-span-2"><x-button type="submit">Ajouter la commande</x-button></div>
                            </form>
                        @endif
                    </div>

                    {{-- Sous-traitance --}}
                    <div x-show="tab === 'sst'" x-cloak class="space-y-4">
                        @forelse ($i->sousTraitances as $s)
                            <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                                <div class="flex items-center justify-between">
                                    <p class="font-medium">{{ $s->nom ?: 'Sous-traitance' }}</p>
                                    @if ($s->retournee)
                                        <x-badge color="#16a34a">Retournée</x-badge>
                                    @else
                                        <form action="{{ route('sous-traitances.update', $s) }}" method="POST">@csrf @method('PUT')
                                            <input type="hidden" name="retournee" value="1">
                                            <button class="text-xs text-brand-600 hover:underline">Marquer retournée</button>
                                        </form>
                                    @endif
                                </div>
                                @if ($s->devis)<p class="text-xs text-gray-400">Devis : {{ $s->devis }}</p>@endif
                            </div>
                        @empty
                            <p class="py-2 text-center text-sm text-gray-400">Aucune sous-traitance</p>
                        @endforelse

                        @if ($peutGerer && ! $i->estCloturee())
                            <form action="{{ route('interventions.sous-traitances.store', $i) }}" method="POST" class="grid grid-cols-2 gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                                @csrf
                                <x-input name="nom" placeholder="Prestataire" />
                                <x-input name="devis" placeholder="N° devis" />
                                <div class="col-span-2"><x-button type="submit">Ajouter</x-button></div>
                            </form>
                        @endif
                    </div>

                    {{-- Historique contact (SMS / e-mails au client pour cette intervention) --}}
                    <div x-show="tab === 'contact'" x-cloak class="space-y-3">
                        @forelse ($i->clientMessages as $msg)
                            <div class="rounded-lg border border-gray-100 p-3 text-sm dark:border-gray-800">
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <span class="flex items-center gap-2">
                                        <x-badge :color="$msg->canal === 'sms' ? '#2563eb' : '#7c3aed'">{{ strtoupper($msg->canal) }}</x-badge>
                                        <span class="text-gray-500">→ {{ $msg->destinataire }}</span>
                                        @if ($msg->statut === 'echec')<x-badge color="#ef4444">Échec</x-badge>@endif
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $msg->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                                @if ($msg->sujet)<p class="font-medium">{{ $msg->sujet }}</p>@endif
                                <p class="whitespace-pre-line text-gray-600 dark:text-gray-300">{{ $msg->corps }}</p>
                            </div>
                        @empty
                            <x-empty-state icon="bell" title="Aucune communication" message="Les SMS et e-mails envoyés au client pour cette intervention apparaîtront ici." />
                        @endforelse
                    </div>

                    {{-- Tchat --}}
                    <div x-show="tab === 'tchat'" x-cloak class="space-y-3">
                        <div class="max-h-80 space-y-3 overflow-y-auto">
                            @forelse ($i->messages as $m)
                                <div class="flex gap-2">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-200 text-[10px] font-semibold dark:bg-gray-700">{{ $m->user?->initials() }}</span>
                                    <div class="rounded-lg bg-gray-100 px-3 py-2 text-sm dark:bg-gray-800">
                                        <p class="text-xs font-medium text-gray-500">{{ $m->user?->fullName() }} · {{ $m->created_at?->diffForHumans() }}</p>
                                        <p class="whitespace-pre-line">{{ $m->message }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="py-4 text-center text-sm text-gray-400">Aucun message</p>
                            @endforelse
                        </div>
                        <form action="{{ route('interventions.chat.store', $i) }}" method="POST" class="flex gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                            @csrf
                            <x-input name="message" placeholder="Votre message…" class="flex-1" />
                            <x-button type="submit">Envoyer</x-button>
                        </form>
                    </div>
                </div>
            </x-card>

            {{-- Restitution (close) --}}
            @if ($peutGerer && ! $i->estCloturee())
                <x-card title="Restituer & clôturer">
                    <form action="{{ route('interventions.restituer', $i) }}" method="POST" class="space-y-4">
                        @csrf
                        <x-field label="Diagnostic / rapport technique" name="diagnostic">
                            <x-textarea name="diagnostic" rows="3">{{ $i->diagnostic }}</x-textarea>
                        </x-field>
                        <x-field label="Message au client" name="message_client">
                            <x-textarea name="message_client" rows="2">{{ $i->message_client }}</x-textarea>
                        </x-field>
                        <div class="flex justify-end"><x-button type="submit">Clôturer l'intervention</x-button></div>
                    </form>
                </x-card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <x-card title="Client">
                <a href="{{ route('clients.show', $i->client) }}" class="font-medium text-brand-600 hover:underline">{{ $i->client?->nomComplet() }}</a>
                <dl class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                    @if ($i->client?->telephone_mobile)<dd>📱 {{ $i->client->telephone_mobile }}</dd>@endif
                    @if ($i->client?->telephone_fixe)<dd>☎ {{ $i->client->telephone_fixe }}</dd>@endif
                    @if ($i->client?->email)<dd>✉ {{ $i->client->email }}</dd>@endif
                    @if ($i->client?->adresseComplete())<dd>📍 {{ $i->client->adresseComplete() }}</dd>@endif
                </dl>
            </x-card>

            <x-card title="Techniciens">
                @forelse ($i->techniciens as $t)
                    <div class="flex items-center gap-2 py-1">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-600 text-[10px] font-semibold text-white">{{ $t->initials() }}</span>
                        <span class="text-sm">{{ $t->fullName() }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Personne</p>
                @endforelse
            </x-card>

            <x-card title="Suivi client (lien sécurisé)">
                <p class="mb-2 text-xs text-gray-500">Le client peut suivre l'avancement en direct via ce lien :</p>
                <div class="flex items-center gap-2" x-data="{ copied: false }">
                    <input readonly value="{{ route('public.intervention', $i->public_token) }}" x-ref="link"
                           class="w-full truncate rounded-lg border-gray-300 bg-gray-50 text-xs dark:border-gray-700 dark:bg-gray-800" />
                    <x-button variant="secondary" type="button" @click="navigator.clipboard.writeText($refs.link.value); copied = true; setTimeout(() => copied = false, 1500)">
                        <span x-text="copied ? 'Copié !' : 'Copier'"></span>
                    </x-button>
                </div>
                <a href="{{ route('public.intervention', $i->public_token) }}" target="_blank" class="mt-2 inline-block text-xs text-brand-600 hover:underline">Ouvrir la page client →</a>
            </x-card>

            @can(\App\Support\Permissions::MESSAGES_SEND)
                <x-card title="Contacter le client">
                    <form action="{{ route('interventions.message_client', $i) }}" method="POST" class="space-y-3" x-data="{ canal: 'sms' }">
                        @csrf
                        <x-select name="canal" x-model="canal">
                            <option value="sms">SMS</option>
                            <option value="email">E-mail</option>
                        </x-select>
                        <x-input name="sujet" placeholder="Sujet (e-mail)" x-show="canal === 'email'" x-cloak />
                        <x-textarea name="corps" rows="3" placeholder="Votre message…" required></x-textarea>
                        <div class="flex justify-end"><x-button type="submit">Envoyer</x-button></div>
                    </form>
                </x-card>
            @endcan

            <x-card title="Journal">
                <ol class="space-y-3">
                    @forelse ($i->logs->take(15) as $log)
                        <li class="flex gap-2 text-sm">
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                            <div>
                                <p><span class="font-medium">{{ $log->user?->prenom ?? 'Système' }}</span> {{ $log->texte }}</p>
                                <p class="text-xs text-gray-400">{{ $log->created_at?->diffForHumans() }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-400">Aucune activité</li>
                    @endforelse
                </ol>
            </x-card>
        </div>
    </div>
@endsection
