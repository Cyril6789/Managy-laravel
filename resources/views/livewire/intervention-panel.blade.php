@php
    $i = $this->intervention;
    $peutGerer = $this->canManage;
    // The "Rapport" tab is an editing surface, only meaningful while the job is open
    // and the user may manage it. When unavailable, the diagnostic / messages are
    // still shown read-only inside "Détails" so nothing is ever hidden.
    $showRapportTab = $peutGerer && ! $i->estCloturee();

    $counts = [
        'details' => 0,
        'rapport' => 0,
        'prestations' => $i->prestations->count() + $i->pieces->count(),
        'photos' => $i->photos->count(),
        'appro' => $i->commandes->count() + $i->sousTraitances->count(),
        'comm' => $i->clientMessages->count(),
    ];

    $tabs = ['details' => 'Détails'];
    if ($showRapportTab) $tabs['rapport'] = 'Rapport';
    $tabs += [
        'prestations' => 'Prestations & pièces',
        'photos' => 'Photos',
        'appro' => 'Approvisionnement',
        'comm' => 'Communication',
    ];
@endphp

{{-- A single tabbed surface: switching a tab swaps the *whole* work area, so there
     are no stray cards left hanging around. `tab` is entangled with the server so a
     Livewire re-render keeps the active tab. --}}
<div x-data="{ tab: @entangle('tab') }" class="space-y-6">

    {{-- Status quick change --}}
    <x-card>
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm text-gray-500">Statut :</span>
            <select wire:model="statutId" wire:change="changeStatut" @disabled(! $peutGerer)
                    class="w-56 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                @foreach ($statuts as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach
            </select>
            <span wire:loading wire:target="changeStatut" class="text-xs text-amber-600">…</span>
            @if ($i->urgente)<x-badge color="#ef4444">Urgent</x-badge>@endif
            @if ($i->garantie)<x-badge>Garantie</x-badge>@endif
            <x-badge>{{ $i->type_lieu === 'domicile' ? 'À domicile' : 'Atelier' }}</x-badge>
        </div>
    </x-card>

    <x-card :padding="false">
        <div class="flex gap-1 overflow-x-auto border-b border-gray-100 px-3 pt-2 dark:border-gray-800">
            @foreach ($tabs as $key => $label)
                <button type="button" @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium">{{ $label }}@if (! empty($counts[$key]))<span class="ml-1 text-xs text-gray-400">({{ $counts[$key] }})</span>@endif</button>
            @endforeach
        </div>

        <div class="p-5">
            {{-- ───────────────────────── Détails (spécificités) ───────────────────────── --}}
            <div x-show="tab === 'details'" class="space-y-4 text-sm">
                {{-- Équipement (matériel, OS, antivirus) --}}
                <div class="grid grid-cols-2 gap-3 rounded-lg border border-gray-100 p-3 dark:border-gray-800 sm:grid-cols-3">
                    <div>
                        <p class="mb-0.5 text-xs font-medium uppercase tracking-wide text-gray-400">Type de matériel</p>
                        <p class="text-gray-700 dark:text-gray-300">{{ $i->materiel?->nom ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="mb-0.5 text-xs font-medium uppercase tracking-wide text-gray-400">Système d'exploitation</p>
                        <p class="text-gray-700 dark:text-gray-300">{{ $i->systemeExploitation?->nom ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="mb-0.5 text-xs font-medium uppercase tracking-wide text-gray-400">Antivirus</p>
                        <p class="text-gray-700 dark:text-gray-300">{{ $i->antivirus?->nom ?: '—' }}</p>
                    </div>
                </div>

                @foreach ([['Matériel déposé', $i->materiel_depose], ['Panne signalée', $i->panne]] as [$label, $value])
                    <div>
                        <p class="mb-0.5 text-xs font-medium uppercase tracking-wide text-gray-400">{{ $label }}</p>
                        <p class="whitespace-pre-line text-gray-700 dark:text-gray-300">{{ $value ?: '—' }}</p>
                    </div>
                @endforeach

                {{-- Accès / mot de passe : c'est une spécificité de la machine, pas un rapport. --}}
                <div class="rounded-lg border border-amber-100 bg-amber-50/50 p-3 dark:border-amber-900/40 dark:bg-amber-900/10">
                    <p class="mb-1 flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-amber-600 dark:text-amber-400">
                        <x-icon name="lock" class="h-3.5 w-3.5" /> Accès / mot de passe
                    </p>
                    @if ($peutGerer && ! $i->estCloturee())
                        <input wire:model.blur="mdp" placeholder="Session, BIOS, comptes…"
                               class="w-full rounded-lg border-gray-300 font-mono text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                        <p class="mt-1 text-xs text-gray-400">
                            <span wire:loading wire:target="mdp" class="text-amber-600">Enregistrement…</span>
                            <span wire:loading.remove>Enregistré automatiquement.</span>
                        </p>
                        @error('mdp')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @else
                        <p class="font-mono text-gray-700 dark:text-gray-300">{{ $i->mdp ?: '—' }}</p>
                    @endif
                </div>

                {{-- Quand l'onglet « Rapport » n'est pas disponible (clôturée / lecture seule),
                     on affiche ici le diagnostic et les messages pour ne rien masquer. --}}
                @unless ($showRapportTab)
                    @foreach ([['Diagnostic / rapport', $i->diagnostic], ['Message au client', $i->message_client], ['Note interne', $i->message_interne]] as [$label, $value])
                        <div>
                            <p class="mb-0.5 text-xs font-medium uppercase tracking-wide text-gray-400">{{ $label }}</p>
                            <p class="whitespace-pre-line text-gray-700 dark:text-gray-300">{{ $value ?: '—' }}</p>
                        </div>
                    @endforeach
                    @if ($i->tarif_estimatif !== null)
                        <div><p class="mb-0.5 text-xs font-medium uppercase tracking-wide text-gray-400">Tarif estimatif</p><p class="text-gray-700 dark:text-gray-300">{{ number_format($i->tarif_estimatif, 2, ',', ' ') }} €</p></div>
                    @endif
                @endunless
            </div>

            {{-- ───────────────────────── Rapport (édition live) ───────────────────────── --}}
            @if ($showRapportTab)
                <div x-show="tab === 'rapport'" x-cloak>
                    <livewire:intervention-report :intervention="$i" :key="'report-'.$i->id" />
                </div>
            @endif

            {{-- ───────────────── Prestations & pièces (matériel ajouté) ───────────────── --}}
            <div x-show="tab === 'prestations'" x-cloak class="space-y-6">
                {{-- Prestations --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Prestations</h3>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($i->prestations as $p)
                                <tr wire:key="presta-{{ $p->id }}">
                                    <td class="py-2">{{ $p->designation }}</td>
                                    <td class="py-2 text-right text-gray-500">{{ rtrim(rtrim(number_format($p->duree, 2), '0'), '.') }} h{{ $p->tarif !== null ? ' × '.number_format($p->tarif, 2, ',', ' ').' €/h' : '' }}</td>
                                    <td class="py-2 pl-3 text-right text-gray-500">{{ $p->tarif !== null ? number_format($p->montant(), 2, ',', ' ').' €' : '—' }}</td>
                                    <td class="py-2 pl-3 text-right">
                                        @if ($peutGerer)
                                            <button wire:click="deletePrestation({{ $p->id }})" wire:confirm="Supprimer ?" class="text-gray-400 hover:text-red-600">&times;</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-gray-400">Aucune prestation</td></tr>
                            @endforelse
                        </tbody>
                        @if ($i->prestations->count())
                            <tfoot><tr class="border-t border-gray-200 font-semibold dark:border-gray-700">
                                <td class="py-2">Total</td>
                                <td class="py-2 text-right">{{ rtrim(rtrim(number_format($i->tempsTotal(), 2), '0'), '.') }} h</td>
                                <td class="py-2 pl-3 text-right">{{ number_format($i->montantPrestations(), 2, ',', ' ') }} €</td>
                                <td></td>
                            </tr></tfoot>
                        @endif
                    </table>

                    @if ($peutGerer && ! $i->estCloturee())
                        <form wire:submit="addPrestation" class="flex flex-wrap items-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                            <x-field label="Prestation" class="flex-1 min-w-48">
                                <x-select wire:model="presta.prestation_id" wire:change="selectPrestation">
                                    <option value="">— Saisie libre —</option>
                                    @foreach ($catalogue as $pc)<option value="{{ $pc->id }}">{{ $pc->designation }}</option>@endforeach
                                </x-select>
                            </x-field>
                            <x-field label="Désignation" class="flex-1 min-w-40"><x-input wire:model="presta.designation" /></x-field>
                            <x-field label="Durée (h)" class="w-24"><x-input wire:model="presta.duree" type="text" inputmode="decimal" /></x-field>
                            <x-button type="submit">Ajouter</x-button>
                        </form>
                        <p class="text-xs text-gray-400">Le tarif horaire est défini dans les paramètres (catalogue de prestations) ; le montant facturé est ce tarif × la durée saisie.</p>
                        @error('presta.designation')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                        @error('presta.duree')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @endif
                </div>

                {{-- Pièces / matériel ajouté (saisie à la volée) --}}
                <div class="space-y-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Pièces &amp; matériel ajouté</h3>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($i->pieces as $p)
                                <tr wire:key="piece-{{ $p->id }}">
                                    <td class="py-2">{{ $p->designation }}</td>
                                    <td class="py-2 text-right text-gray-500">{{ rtrim(rtrim(number_format($p->quantite, 2), '0'), '.') }} × {{ number_format($p->prix, 2, ',', ' ') }} €</td>
                                    <td class="py-2 pl-3 text-right">{{ number_format($p->total(), 2, ',', ' ') }} €</td>
                                    <td class="py-2 pl-3 text-right">
                                        @if ($peutGerer)
                                            <button wire:click="deletePiece({{ $p->id }})" wire:confirm="Supprimer ?" class="text-gray-400 hover:text-red-600">&times;</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-gray-400">Aucune pièce</td></tr>
                            @endforelse
                        </tbody>
                        @if ($i->pieces->count())
                            <tfoot><tr class="border-t border-gray-200 font-semibold dark:border-gray-700">
                                <td class="py-2" colspan="2">Total pièces</td>
                                <td class="py-2 pl-3 text-right">{{ number_format($i->montantPieces(), 2, ',', ' ') }} €</td>
                                <td></td>
                            </tr></tfoot>
                        @endif
                    </table>

                    @if ($peutGerer && ! $i->estCloturee())
                        <form wire:submit="addPiece" class="flex flex-wrap items-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                            <x-field label="Pièce remplacée" class="flex-1 min-w-48"><x-input wire:model="piece.designation" placeholder="Ex. Disque SSD 500 Go" /></x-field>
                            <x-field label="Qté" class="w-20"><x-input wire:model="piece.quantite" type="text" inputmode="decimal" /></x-field>
                            <x-field label="Prix unit. (€)" class="w-28"><x-input wire:model="piece.prix" type="text" inputmode="decimal" /></x-field>
                            <x-button type="submit">Ajouter</x-button>
                        </form>
                        @error('piece.designation')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                        @error('piece.prix')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                        @error('piece.quantite')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @endif
                </div>

                {{-- Total global prestations + pièces --}}
                @if ($i->prestations->count() || $i->pieces->count())
                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 text-sm font-semibold dark:bg-gray-800/60">
                        <span>Total prestations &amp; pièces</span>
                        <span>{{ number_format($i->montantPrestations() + $i->montantPieces(), 2, ',', ' ') }} €</span>
                    </div>
                @endif
            </div>

            {{-- ───────────────────────────── Photos ───────────────────────────── --}}
            <div x-show="tab === 'photos'" x-cloak>
                <livewire:intervention-photos :intervention="$i" :key="'photos-'.$i->id" />
            </div>

            {{-- ─────────────────── Approvisionnement (commandes + SST) ─────────────────── --}}
            <div x-show="tab === 'appro'" x-cloak class="space-y-6">
                {{-- Commandes fournisseur --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Commandes fournisseur</h3>
                    @forelse ($i->commandes as $c)
                        <div wire:key="cmd-{{ $c->id }}" class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-medium">{{ $c->fournisseur ?: 'Commande' }} {{ $c->numero_commande ? '· '.$c->numero_commande : '' }}</p>
                                <div class="flex items-center gap-2">
                                    @if ($c->recue)<x-badge color="#16a34a">Reçue</x-badge>
                                    @elseif ($peutGerer)<button wire:click="receiveCommande({{ $c->id }})" class="text-xs text-brand-600 hover:underline">Marquer reçue</button>@endif
                                    @if ($peutGerer)<button wire:click="deleteCommande({{ $c->id }})" wire:confirm="Supprimer ?" class="text-gray-300 hover:text-red-600">&times;</button>@endif
                                </div>
                            </div>
                            @if ($c->suivi_colis)<p class="text-xs text-gray-400">Suivi : {{ $c->suivi_colis }}</p>@endif
                        </div>
                    @empty
                        <p class="py-2 text-center text-sm text-gray-400">Aucune commande</p>
                    @endforelse

                    @if ($peutGerer && ! $i->estCloturee())
                        <form wire:submit="addCommande" class="grid grid-cols-2 gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                            <x-input wire:model="commande.fournisseur" placeholder="Fournisseur" />
                            <x-input wire:model="commande.numero_commande" placeholder="N° commande" />
                            <x-input wire:model="commande.suivi_colis" placeholder="Suivi colis" class="col-span-2" />
                            <div class="col-span-2"><x-button type="submit">Ajouter la commande</x-button></div>
                        </form>
                    @endif
                </div>

                {{-- Sous-traitance --}}
                <div class="space-y-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Sous-traitance</h3>
                    @forelse ($i->sousTraitances as $s)
                        <div wire:key="sst-{{ $s->id }}" class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-medium">{{ $s->nom ?: 'Sous-traitance' }}</p>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('interventions.sst_sheet', [$i, $s]) }}" target="_blank" class="text-xs text-gray-500 hover:text-brand-600 hover:underline">Feuille</a>
                                    @if ($s->retournee)<x-badge color="#16a34a">Retournée</x-badge>
                                    @elseif ($peutGerer)<button wire:click="returnSousTraitance({{ $s->id }})" class="text-xs text-brand-600 hover:underline">Marquer retournée</button>@endif
                                    @if ($peutGerer)<button wire:click="deleteSousTraitance({{ $s->id }})" wire:confirm="Supprimer ?" class="text-gray-300 hover:text-red-600">&times;</button>@endif
                                </div>
                            </div>
                            @if ($s->devis)<p class="text-xs text-gray-400">Devis : {{ $s->devis }}</p>@endif
                        </div>
                    @empty
                        <p class="py-2 text-center text-sm text-gray-400">Aucune sous-traitance</p>
                    @endforelse

                    @if ($peutGerer && ! $i->estCloturee())
                        <form wire:submit="addSousTraitance" class="grid grid-cols-2 gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                            <x-input wire:model="sst.nom" placeholder="Prestataire" />
                            <x-input wire:model="sst.devis" placeholder="N° devis" />
                            <div class="col-span-2"><x-button type="submit">Ajouter</x-button></div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- ───────────────── Communication (tchat + historique) ───────────────── --}}
            <div x-show="tab === 'comm'" x-cloak x-data="{ sub: 'tchat' }" class="space-y-4">
                <div class="inline-flex rounded-lg border border-gray-200 p-0.5 text-sm dark:border-gray-700">
                    <button type="button" @click="sub = 'tchat'"
                            :class="sub === 'tchat' ? 'bg-brand-600 text-white' : 'text-gray-500 hover:text-gray-700'"
                            class="rounded-md px-3 py-1 font-medium">Tchat</button>
                    <button type="button" @click="sub = 'historique'"
                            :class="sub === 'historique' ? 'bg-brand-600 text-white' : 'text-gray-500 hover:text-gray-700'"
                            class="rounded-md px-3 py-1 font-medium">Historique{{ $counts['comm'] ? ' ('.$counts['comm'].')' : '' }}</button>
                </div>

                {{-- Tchat = conversation deux sens avec le client (identique à la page de suivi) --}}
                <div x-show="sub === 'tchat'">
                    <livewire:client-chat :intervention="$i" :key="'chat-'.$i->id" />
                </div>

                {{-- Historique des SMS / e-mails envoyés au client --}}
                <div x-show="sub === 'historique'" x-cloak class="space-y-3">
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
                        <x-empty-state icon="bell" title="Aucune communication" message="Les SMS et e-mails envoyés au client apparaîtront ici." />
                    @endforelse
                </div>
            </div>
        </div>
    </x-card>
</div>
