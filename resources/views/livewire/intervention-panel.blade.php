@php $i = $this->intervention; $peutGerer = $this->canManage; @endphp
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
        @php
            $counts = [
                'prestations' => $i->prestations->count(),
                'pieces' => $i->pieces->count(),
                'commandes' => $i->commandes->count(),
                'sst' => $i->sousTraitances->count(),
                'contact' => $i->clientMessages->count(),
            ];
        @endphp
        <div class="flex gap-1 overflow-x-auto border-b border-gray-100 px-3 pt-2 dark:border-gray-800">
            @foreach (['details' => 'Détails', 'prestations' => 'Prestations', 'pieces' => 'Pièces', 'commandes' => 'Commandes', 'sst' => 'Sous-traitance', 'contact' => 'Contact', 'tchat' => 'Tchat'] as $key => $label)
                <button type="button" @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium">{{ $label }}@if (! empty($counts[$key]))<span class="ml-1 text-xs text-gray-400">({{ $counts[$key] }})</span>@endif</button>
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
                            <tr wire:key="presta-{{ $p->id }}">
                                <td class="py-2">{{ $p->designation }}</td>
                                <td class="py-2 text-right text-gray-500">{{ rtrim(rtrim(number_format($p->duree, 2), '0'), '.') }} h</td>
                                <td class="py-2 pl-3 text-right text-gray-500">{{ $p->tarif !== null ? number_format($p->tarif, 2, ',', ' ').' €' : '—' }}</td>
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
                        <x-field label="Durée (h)" class="w-24"><x-input wire:model="presta.duree" type="number" step="0.25" /></x-field>
                        <x-button type="submit">Ajouter</x-button>
                    </form>
                    <p class="text-xs text-gray-400">Le tarif est défini dans les paramètres (catalogue de prestations).</p>
                    @error('presta.designation')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('presta.duree')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                @endif
            </div>

            {{-- Pièces remplacées (saisie à la volée) --}}
            <div x-show="tab === 'pieces'" x-cloak class="space-y-4">
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
                        <x-field label="Qté" class="w-20"><x-input wire:model="piece.quantite" type="number" step="0.01" min="0.01" /></x-field>
                        <x-field label="Prix unit. (€)" class="w-28"><x-input wire:model="piece.prix" type="number" step="0.01" min="0" /></x-field>
                        <x-button type="submit">Ajouter</x-button>
                    </form>
                    @error('piece.designation')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('piece.prix')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('piece.quantite')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                @endif
            </div>

            {{-- Commandes --}}
            <div x-show="tab === 'commandes'" x-cloak class="space-y-4">
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
            <div x-show="tab === 'sst'" x-cloak class="space-y-4">
                @forelse ($i->sousTraitances as $s)
                    <div wire:key="sst-{{ $s->id }}" class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-medium">{{ $s->nom ?: 'Sous-traitance' }}</p>
                            <div class="flex items-center gap-2">
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

            {{-- Contact (SMS / e-mails) --}}
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
                    <x-empty-state icon="bell" title="Aucune communication" message="Les SMS et e-mails envoyés au client apparaîtront ici." />
                @endforelse
            </div>

            {{-- Tchat = conversation avec le client (identique à la page de suivi) --}}
            <div x-show="tab === 'tchat'" x-cloak>
                <livewire:client-chat :intervention="$i" :key="'chat-'.$i->id" />
            </div>
        </div>
    </x-card>
</div>
