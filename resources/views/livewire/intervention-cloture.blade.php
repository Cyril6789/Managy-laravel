{{-- Closing card — recomputed live when prestations / pièces change (see InterventionCloture). --}}
<div>
    {{-- Workshop: the repair must be marked "finalisée" before restitution. --}}
    @if (! $i->estDomicile() && ! $i->estFinalisee())
        <x-card title="Intervention finalisée ?">
            <p class="mb-3 text-sm text-gray-500">Une fois la machine réparée en atelier, marquez l'intervention comme <strong>finalisée</strong>. Le bouton « Restituer &amp; clôturer » apparaîtra alors, pour quand le client viendra récupérer son matériel à la boutique.</p>
            <div class="mb-3 rounded-lg border border-gray-100 p-3 text-sm dark:border-gray-800">
                <div class="flex justify-between"><span class="text-gray-500">Prestations{{ $breakdown['prestations_remise_pct'] > 0 ? ' (−'.rtrim(rtrim(number_format($breakdown['prestations_remise_pct'],2),'0'),'.').' %)' : '' }}</span><span>{{ number_format($breakdown['prestations_net'], 2, ',', ' ') }} €</span></div>
                @if ($breakdown['pieces_net'] > 0)<div class="flex justify-between"><span class="text-gray-500">Pièces{{ $breakdown['pieces_remise_pct'] > 0 ? ' (−'.rtrim(rtrim(number_format($breakdown['pieces_remise_pct'],2),'0'),'.').' %)' : '' }}</span><span>{{ number_format($breakdown['pieces_net'], 2, ',', ' ') }} €</span></div>@endif
                <div class="mt-1 flex justify-between border-t border-gray-100 pt-1 font-semibold dark:border-gray-800"><span>Total</span><span>{{ number_format($breakdown['total'], 2, ',', ' ') }} €</span></div>
            </div>
            <form action="{{ route('interventions.finaliser', $i) }}" method="POST">@csrf
                <x-button type="submit">Intervention finalisée</x-button>
            </form>
        </x-card>
    @else
        <x-card title="Restituer & clôturer">
            @if (! $i->estDomicile())
                <div class="mb-3 flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-900/30 dark:text-blue-300">
                    <x-icon name="check" class="h-4 w-4" /> Intervention finalisée{{ $i->finalisee_at ? ' le '.$i->finalisee_at->format('d/m/Y à H:i') : '' }}.
                    <form action="{{ route('interventions.annuler_finalisation', $i) }}" method="POST" class="ml-auto">@csrf
                        <button class="text-xs underline hover:text-blue-600">Annuler la finalisation</button>
                    </form>
                </div>
            @endif

            <p class="mb-3 text-sm text-gray-500">
                @if ($i->estDomicile())
                    Faites signer la fiche au client. Le montant est calculé à partir des prestations et pièces saisies ; ajoutez le déplacement et indiquez si le client a déjà payé.
                @else
                    Quand le client vient récupérer son matériel, faites-le signer puis clôturez. Les heures saisies seront déduites du pack maintenance et une copie signée lui sera envoyée par e-mail.
                @endif
            </p>

            {{-- The wire:key embeds the live amounts: when they change, Livewire replaces
                 this node, so the `restitution` Alpine state re-initialises with the new
                 prestations / pièces totals. --}}
            <form wire:key="restitution-{{ $breakdown['prestations_net'] }}-{{ $breakdown['pieces_net'] }}-{{ $breakdown['deplacement'] }}"
                  action="{{ route('interventions.restituer', $i) }}" method="POST" @submit="beforeSubmit()"
                  x-data="restitution({
                      lieu: '{{ $i->type_lieu }}',
                      prestaNet: {{ $breakdown['prestations_net'] }},
                      piecesNet: {{ $breakdown['pieces_net'] }},
                      deplMode: '{{ $deplMode }}',
                      deplGratuit: {{ $deplGratuit ? 'true' : 'false' }},
                      deplForfait: {{ $deplForfait ?: 0 }},
                      deplPrixKm: {{ $deplPrixKm ?: 0 }},
                      deplDefault: {{ $breakdown['deplacement'] }},
                      canRistourne: {{ ($peutRistourne && $i->estDomicile() && ! $i->garantie) ? 'true' : 'false' }},
                      hasPack: {{ ($maintenanceHasPack && ! $i->garantie) ? 'true' : 'false' }},
                      packSolde: {{ $maintenanceSolde }},
                      totalHeures: {{ $totalHeures }},
                      needsPrepare: {{ ($i->estDomicile() && ! $i->garantie) ? 'true' : 'false' }},
                  })" class="space-y-4">
                @csrf
                <input type="hidden" name="signature" :value="value">
                {{-- Montants (toujours soumis, jamais montrés au client) --}}
                <input type="hidden" name="remise_type" :value="remiseType">
                <input type="hidden" name="remise_valeur" :value="canRistourne ? remiseValeur : 0">
                <input type="hidden" name="deplacement_km" :value="waiveDepl ? 0 : km">
                <input type="hidden" name="montant_deplacement" :value="effectiveDepl">
                <input type="hidden" name="maintenance_heures" :value="hasPack ? packHeures : 0">

                @if ($i->garantie)
                    <div class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700 dark:border-green-900 dark:bg-green-900/30 dark:text-green-300">
                        <x-icon name="shield" class="mr-1 inline h-4 w-4" /> Intervention sous garantie : prestation offerte, montant à 0 €.
                    </div>
                @endif

                {{-- ÉTAPE 1 — Réglage des montants par le technicien (hors de la vue du client) --}}
                @if ($i->estDomicile() && ! $i->garantie)
                    <div x-show="!signing" class="space-y-4">
                        <p class="text-sm text-gray-500">Réglez les montants <strong>avant</strong> de présenter l'écran de signature au client : la ristourne et l'offre du déplacement ne lui seront pas affichées.</p>

                        @if ($peutRistourne)
                            <div>
                                <span class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Ristourne (facultatif)</span>
                                <div class="flex gap-2">
                                    <input type="text" inputmode="decimal" x-model="remiseValeur" placeholder="0"
                                           class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                                    <select x-model="remiseType" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                                        <option value="euro">€</option>
                                        <option value="pourcent">%</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-3">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" x-model="waiveDepl" class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                                Offrir le déplacement
                            </label>
                            <div x-show="!waiveDepl" x-cloak class="space-y-3">
                                <template x-if="deplMode === 'km' && !deplGratuit">
                                    <x-field label="Distance (km)">
                                        <x-input type="text" inputmode="decimal" x-model="km" @input="onKm()" />
                                    </x-field>
                                </template>
                                <x-field label="Déplacement (€)">
                                    <x-input type="text" inputmode="decimal" x-model="deplacement" />
                                    <p class="mt-1 text-xs text-gray-400" x-show="deplGratuit" x-cloak>Déplacement gratuit pour ce client / cette ville.</p>
                                </x-field>
                            </div>
                        </div>

                        {{-- Pack maintenance : règle tout ou partie des HEURES de prestations
                             (jamais les pièces ni le déplacement). Facultatif. --}}
                        <div x-show="hasPack && totalHeures > 0" x-cloak>
                            <span class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Régler des prestations avec le pack maintenance (facultatif)</span>
                            <div class="flex items-center gap-2">
                                <input type="text" inputmode="decimal" x-model="packHeures" @input="clampPack()" @blur="clampPack()"
                                       class="block w-28 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                                <span class="text-sm text-gray-500">h <span x-text="'/ ' + fmtH(packMax) + ' dispo'"></span></span>
                            </div>
                            <p class="mt-1 text-xs text-gray-400" x-show="packCovered > 0" x-cloak x-text="'− ' + fmt(packCovered) + ' déduits des prestations (réglés par le pack).'"></p>
                            <p class="mt-1 text-xs text-gray-400" x-show="packCovered <= 0" x-cloak>Laissez à 0 pour que le client règle tout en argent (le pack ne sera pas débité).</p>
                        </div>

                        {{-- Récap détaillé réservé au technicien (avec la ristourne) --}}
                        <div class="rounded-lg border border-gray-200 p-3 text-sm dark:border-gray-700">
                            <div class="flex justify-between"><span class="text-gray-500">Sous-total</span><span x-text="fmt(sousTotal)"></span></div>
                            <div class="flex justify-between" x-show="remiseMontant > 0" x-cloak><span class="text-gray-500">Ristourne</span><span x-text="'− ' + fmt(remiseMontant)"></span></div>
                            <div class="flex justify-between" x-show="packCovered > 0" x-cloak><span class="text-gray-500">Pack maintenance</span><span x-text="'− ' + fmt(packCovered)"></span></div>
                            <div class="flex justify-between" x-show="effectiveDepl > 0" x-cloak><span class="text-gray-500">Déplacement</span><span x-text="fmt(effectiveDepl)"></span></div>
                            <div class="mt-1 flex items-center justify-between border-t border-gray-100 pt-2 text-base font-semibold dark:border-gray-800"><span>Total à régler</span><span x-text="fmt(total)"></span></div>
                        </div>

                        <div class="flex justify-end">
                            <x-button type="button" @click="signing = true">Continuer vers la signature →</x-button>
                        </div>
                    </div>
                @endif

                {{-- ÉTAPE 2 — Écran de signature présenté au client --}}
                <div x-show="signing" class="space-y-4">
                    @if ($i->estDomicile() && ! $i->garantie)
                        <button type="button" @click="signing = false" class="text-xs text-gray-500 hover:text-brand-600">← Modifier les montants</button>
                    @endif

                    {{-- Récapitulatif client : prestations, pièces, déplacement et total — aucune mention de ristourne --}}
                    <div class="rounded-lg border border-gray-200 p-3 text-sm dark:border-gray-700">
                        <div class="flex justify-between"><span class="text-gray-500">Prestations</span><span x-text="fmt(prestaNet)"></span></div>
                        <div class="flex justify-between" x-show="piecesNet > 0" x-cloak><span class="text-gray-500">Pièces</span><span x-text="fmt(piecesNet)"></span></div>
                        <div class="flex justify-between" x-show="packCovered > 0" x-cloak><span class="text-gray-500">Réglé par pack maintenance</span><span x-text="'− ' + fmt(packCovered)"></span></div>
                        <div class="flex justify-between" x-show="isDomicile && effectiveDepl > 0" x-cloak><span class="text-gray-500">Déplacement</span><span x-text="fmt(effectiveDepl)"></span></div>
                        <div class="mt-1 flex items-center justify-between border-t border-gray-100 pt-2 text-base font-semibold dark:border-gray-800">
                            <span>Total</span><span x-text="fmt(total)"></span>
                        </div>
                    </div>

                    <x-field label="Nom du signataire" name="signataire_nom">
                        <x-input name="signataire_nom" value="{{ $i->client?->nomComplet() }}" />
                    </x-field>
                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Signature du client</span>
                            <button type="button" @click="clear()" class="text-xs text-gray-500 hover:text-red-600">Effacer</button>
                        </div>
                        <canvas x-ref="canvas"
                                @mousedown="start($event)" @mousemove="move($event)" @mouseup="end()" @mouseleave="end()"
                                @touchstart="start($event)" @touchmove="move($event)" @touchend="end()"
                                class="h-40 w-full touch-none rounded-lg border-2 border-dashed border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-200"></canvas>
                        <p class="mt-1 text-xs text-gray-400">Signez ci-dessus avec le doigt ou la souris (facultatif).</p>
                    </div>

                    @if ($i->estDomicile())
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" x-model="payee" class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                                Le client a déjà payé
                            </label>
                            <input type="hidden" name="payee" :value="payee ? 1 : 0">
                            <div x-show="payee" x-cloak class="grid grid-cols-2 gap-3">
                                <x-field label="Montant payé (€)">
                                    <x-input type="text" inputmode="decimal" x-model="montantPaye" />
                                    <input type="hidden" name="montant_paye" :value="payee ? montantPayeNet : ''">
                                </x-field>
                                <x-field label="Moyen de paiement">
                                    <x-select name="paiement_mode" x-model="paiementMode">
                                        @foreach (['especes' => 'Espèces', 'cb' => 'Carte bancaire', 'cheque' => 'Chèque', 'virement' => 'Virement', 'autre' => 'Autre'] as $v => $l)
                                            <option value="{{ $v }}">{{ $l }}</option>
                                        @endforeach
                                    </x-select>
                                </x-field>
                            </div>
                        </div>
                    @else
                        {{-- Pack maintenance (atelier) : règle tout ou partie des HEURES de prestations. --}}
                        <div x-show="hasPack && totalHeures > 0" x-cloak>
                            <span class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Régler des prestations avec le pack maintenance (facultatif)</span>
                            <div class="flex items-center gap-2">
                                <input type="text" inputmode="decimal" x-model="packHeures" @input="clampPack()" @blur="clampPack()"
                                       class="block w-28 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                                <span class="text-sm text-gray-500">h <span x-text="'/ ' + fmtH(packMax) + ' dispo'"></span></span>
                            </div>
                            <p class="mt-1 text-xs text-gray-400" x-show="packCovered > 0" x-cloak x-text="'− ' + fmt(packCovered) + ' réglés par le pack.'"></p>
                            <p class="mt-1 text-xs text-gray-400" x-show="packCovered <= 0" x-cloak>Laissez à 0 pour ne pas débiter le pack.</p>
                        </div>

                        {{-- Atelier : pas de ristourne, simple indication de facturation --}}
                        <div>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" x-model="facturee" class="rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                                L'intervention a déjà été facturée
                            </label>
                            <input type="hidden" name="facturee" :value="facturee ? 1 : 0">
                            <p class="mt-1 text-xs text-gray-400">Sinon, elle apparaîtra dans la page « À facturer ».</p>
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <x-button type="submit">Restituer &amp; clôturer</x-button>
                    </div>
                </div>
            </form>
        </x-card>
    @endif
</div>
