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
            <livewire:intervention-panel :intervention="$i" :key="'panel-'.$i->id" />

            {{-- Technical report (live auto-save, no page reload — Livewire) --}}
            @if ($peutGerer && ! $i->estCloturee())
                <x-card title="Rapport technique">
                    <livewire:intervention-report :intervention="$i" :key="'report-'.$i->id" />
                </x-card>

                <x-card title="Clôturer l'intervention">
                    <p class="mb-3 text-sm text-gray-500">Enregistrez d'abord le rapport ci-dessus, puis clôturez. Les heures saisies seront déduites du pack maintenance le cas échéant.</p>
                    <form action="{{ route('interventions.restituer', $i) }}" method="POST" onsubmit="return confirm('Clôturer définitivement cette intervention ?')">
                        @csrf
                        <x-button type="submit">Restituer & clôturer</x-button>
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
