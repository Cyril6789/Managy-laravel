@extends('layouts.app')
@section('title', $intervention->reference)

@php
    $i = $intervention;
    $estTech = $i->techniciens->contains(auth()->id());
    $verrou = $i->estVerrouillee();
    $peutGerer = auth()->user()->can(\App\Support\Permissions::INTERVENTIONS_MANAGE);
    // RDV + affectation : réservé aux interventions à domicile (l'atelier n'a pas
    // de rendez-vous, le bloc « Techniciens » suffit). Le bloc « Techniciens » est
    // alors masqué pour le domicile, pour ne pas faire doublon.
    $afficheRdv = $peutGerer && ! $i->estCloturee() && $i->estDomicile();
@endphp

@section('content')
    <x-page-header :title="'Intervention '.$i->reference" :subtitle="$i->client?->nomComplet()">
        <x-slot:actions>
            @if ($i->type_lieu === 'domicile')
                <span class="inline-flex items-center gap-1 rounded-lg bg-purple-50 px-3 py-1.5 text-sm font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-300"><x-icon name="home" class="h-4 w-4" /> Sur site / domicile</span>
            @else
                <span class="inline-flex items-center gap-1 rounded-lg bg-sky-50 px-3 py-1.5 text-sm font-medium text-sky-700 dark:bg-sky-900/30 dark:text-sky-300"><x-icon name="wrench" class="h-4 w-4" /> Atelier</span>
            @endif
            <x-button variant="secondary" :href="route('interventions.print', [$i, 'depot'])" target="_blank">Fiche dépôt</x-button>
            <x-button variant="secondary" :href="route('interventions.print', [$i, 'rapport'])" target="_blank">Rapport</x-button>
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
            @if ($i->montant_total !== null)
                @php
                    $details = [];
                    if ($i->montant_pieces) $details[] = number_format($i->montant_pieces, 2, ',', ' ').' € de pièces';
                    if ($i->montant_deplacement) $details[] = number_format($i->montant_deplacement, 2, ',', ' ').' € de déplacement';
                    if ($i->remise_montant) $details[] = 'ristourne '.number_format($i->remise_montant, 2, ',', ' ').' €';
                    if ($i->montant_maintenance) $details[] = number_format($i->montant_maintenance, 2, ',', ' ').' € réglés par pack maintenance';
                @endphp
                <span class="font-medium">— {{ number_format($i->montant_total, 2, ',', ' ') }} €{{ $details ? ' (dont '.implode(', ', $details).')' : '' }}</span>
                @if ($i->payee)<span>· payé{{ $i->paiement_mode ? ' par '.$i->paiement_mode : '' }}</span>@endif
            @endif
            @can(\App\Support\Permissions::INTERVENTIONS_FACTURATION)
                <form action="{{ route('interventions.facturation', $i) }}" method="POST" class="ml-auto">@csrf
                    <button class="font-medium underline">{{ $i->facturee ? 'Marquée facturée ✓' : 'Marquer comme facturée' }}</button>
                </form>
            @endcan
        </div>
    @endif

    @if ($maintenance['has'])
        @php $low = $maintenance['balance'] < $maintenance['threshold']; @endphp
        <div class="mb-4 flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm
            {{ $low ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-900/30 dark:text-red-300'
                    : 'border-green-200 bg-green-50 text-green-700 dark:border-green-900 dark:bg-green-900/30 dark:text-green-300' }}">
            <x-icon name="shield" class="h-4 w-4" />
            <span><span class="font-semibold">Pack maintenance :</span> {{ number_format($maintenance['balance'], 2) }} h restantes</span>
            @if ($low)<span class="font-medium">— ⚠️ solde sous le seuil de {{ rtrim(rtrim(number_format($maintenance['threshold'],2),'0'),'.') }} h</span>@endif
            <a href="{{ route('maintenance.show', $i->client) }}" class="ml-auto underline">Détail</a>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main panel --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Tout est désormais regroupé dans les onglets du panneau (Détails, Rapport,
                 Prestations & pièces, Photos, Approvisionnement, Communication) : changer
                 d'onglet remplace toute la zone de travail, plus de cards « fantômes ». --}}
            <livewire:intervention-panel :intervention="$i" :key="'panel-'.$i->id" />

            {{-- Clôture : la seule action qui reste hors des onglets, car c'est le bouton
                 de fin d'intervention (finalisation puis restitution & signature). --}}
            @if ($peutGerer && ! $i->estCloturee())
                <livewire:intervention-cloture :intervention="$i" :key="'cloture-'.$i->id" />
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

            @if ($afficheRdv)
                <x-card title="Rendez-vous & affectation">
                    <p class="mb-3 text-xs text-gray-500">Modifiez la date du rendez-vous et choisissez le(s) technicien(s) en fonction des disponibilités de chacun.</p>
                    <livewire:intervention-schedule mode="live" :intervention="$i" :key="'sched-'.$i->id" />
                </x-card>
            @endif

            @unless ($afficheRdv)
            <x-card title="Techniciens">
                @php $assignables = $techniciens->whereNotIn('id', $i->techniciens->pluck('id')); @endphp
                @forelse ($i->techniciens as $t)
                    <div class="flex items-center gap-2 py-1">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-600 text-[10px] font-semibold text-white">{{ $t->initials() }}</span>
                        <span class="flex-1 text-sm">{{ $t->fullName() }}</span>
                        @can(\App\Support\Permissions::INTERVENTIONS_ASSIGN)
                            <form action="{{ route('interventions.assign', $i) }}" method="POST">@csrf
                                <input type="hidden" name="user_id" value="{{ $t->id }}">
                                <input type="hidden" name="action" value="remove">
                                <button class="text-gray-300 hover:text-red-600" title="Retirer">&times;</button>
                            </form>
                        @endcan
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Personne</p>
                @endforelse

                @can(\App\Support\Permissions::INTERVENTIONS_ASSIGN)
                    @if ($assignables->isNotEmpty())
                        <form action="{{ route('interventions.assign', $i) }}" method="POST" class="mt-3 flex gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                            @csrf
                            <input type="hidden" name="action" value="add">
                            <x-searchable-select name="user_id" :options="$assignables->mapWithKeys(fn ($u) => [$u->id => $u->fullName()])"
                                :allow-empty="false" placeholder="Affecter…" class="flex-1" />
                            <x-button type="submit">Affecter</x-button>
                        </form>
                    @endif
                @endcan
            </x-card>
            @endunless

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
                    <form action="{{ route('interventions.message_client', $i) }}" method="POST" class="space-y-3"
                          x-data="messageComposer({ sms: @js($smsTypes), email: @js($mailTypes) })">
                        @csrf
                        <x-select name="canal" x-model="canal">
                            <option value="sms">SMS</option>
                            <option value="email">E-mail</option>
                        </x-select>
                        <template x-if="templates.length">
                            <x-select x-model="typeId" @change="applyType()">
                                <option value="">— Modèle —</option>
                                <template x-for="t in templates" :key="t.id">
                                    <option :value="t.id" x-text="t.titre"></option>
                                </template>
                            </x-select>
                        </template>
                        <x-input name="sujet" placeholder="Sujet (e-mail)" x-model="sujet" x-show="canal === 'email'" x-cloak />
                        <x-textarea name="corps" rows="3" placeholder="Votre message…" x-model="corps" required></x-textarea>
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
