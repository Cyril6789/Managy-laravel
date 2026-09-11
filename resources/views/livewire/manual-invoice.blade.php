<div>
    @if ($launcher)
        <x-button type="button" wire:click="open">+ Nouvelle facture</x-button>
    @endif

    @if ($show)
        <div class="fixed inset-0 z-[90] flex items-start justify-center overflow-y-auto bg-black/60 p-4 py-8" wire:key="manual-invoice-modal" wire:click.self="$set('show', false)" x-on:keydown.escape.window="$wire.set('show', false)">
            <div class="max-h-[94vh] w-full max-w-[95rem] overflow-y-auto rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <div><h2 class="text-lg font-semibold">Préparer la facture</h2><p class="text-sm text-gray-500">{{ $interventionId ? 'Lignes préremplies depuis l’intervention. Vérifiez-les avant émission.' : 'Facture libre, sans intervention associée.' }}</p></div>
                    <button type="button" wire:click="$set('show', false)" class="text-2xl text-gray-400">×</button>
                </div>

                <x-field label="Client" required>
                    <x-searchable-select name="manual_invoice_client" wire:model.change="clientId" :selected="$clientId"
                        :options="$clients->mapWithKeys(fn ($client) => [$client->id => $client->nomComplet()])" :disabled="(bool) $interventionId"
                        :allow-empty="false" placeholder="Choisir un client…" search-placeholder="Rechercher un client…" />
                    @error('clientId')<p class="mt-1 text-xs text-red-600">Sélectionnez un client.</p>@enderror
                </x-field>

                <div class="mt-6 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                        <x-field label="Catalogue" class="md:col-span-4">
                            <x-searchable-select name="manual_catalogue" wire:model.change="draft.catalogue_id" wire:change="selectCatalogue"
                                :selected="$draft['catalogue_id']" :options="$catalogue->pluck('designation', 'id')"
                                placeholder="— Ligne libre —" search-placeholder="Rechercher une prestation…" />
                        </x-field>
                        <x-field label="Désignation" class="md:col-span-5"><x-input wire:model="draft.description" /></x-field>
                        <x-field label="Qté" class="md:col-span-1"><x-input type="number" min="0.01" step="0.01" wire:model="draft.quantity" /></x-field>
                        <x-field label="Unité" class="md:col-span-2"><x-input wire:model="draft.unit" placeholder="u, h…" /></x-field>
                        <x-field label="Prix unitaire" class="md:col-span-3"><x-input type="number" min="0" step="0.01" wire:model="draft.unit_price" /></x-field>
                        <x-field label="Prix saisi en" class="md:col-span-2">
                            <x-searchable-select name="manual_price_mode" wire:model.change="draft.price_mode" :selected="$draft['price_mode']" :allow-empty="false" :options="['ht' => 'HT', 'ttc' => 'TTC']" />
                        </x-field>
                        <x-field label="TVA (%)" class="md:col-span-2"><x-input type="number" min="0" max="100" step="0.01" wire:model="draft.vat_rate" :disabled="! $vatEnabled" /></x-field>
                        <div class="flex items-end gap-2 md:col-span-5">
                            <x-button type="button" variant="secondary" wire:click="addTravel">Frais de déplacement</x-button>
                            <x-button type="button" wire:click="addLine">Ajouter la ligne</x-button>
                        </div>
                    </div>
                    @error('draft.description')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('draft.unit_price')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mt-6 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800"><tr><th class="px-3 py-2 text-left">Désignation</th><th class="px-3 py-2 text-right">Qté</th><th class="px-3 py-2 text-left">Unité</th><th class="px-3 py-2 text-right">PU HT</th><th class="px-3 py-2 text-right">TVA</th><th class="px-3 py-2 text-right">Remise</th><th class="px-3 py-2 text-right">Total HT</th><th></th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800" x-data="{ dragging: null }">
                            @forelse ($lines as $index => $line)
                                @php
                                    $lineGross = (float) $line['quantity'] * (float) $line['unit_price_ht'];
                                    $discountValue = (float) ($line['discount_value'] ?? 0);
                                    $discountAmount = ($line['discount_type'] ?? '') === 'pourcent'
                                        ? $lineGross * min($discountValue, 100) / 100
                                        : min(max($lineGross, 0), $discountValue);
                                @endphp
                                <tr wire:key="manual-line-{{ $index }}" x-on:dragover.prevent x-on:drop.prevent="$wire.moveLine(dragging, {{ $index }}); dragging = null" class="transition hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                    <td class="min-w-52 px-3 py-2"><div class="flex items-center gap-2"><span draggable="true" x-on:dragstart="dragging = {{ $index }}" class="cursor-grab select-none text-lg text-gray-300" title="Glisser pour réordonner">⠿</span><x-input wire:model.live.debounce.400ms="lines.{{ $index }}.description" /></div></td>
                                    <td class="w-24 px-3 py-2"><x-input type="number" min="0.01" step="0.01" wire:model.live.debounce.300ms="lines.{{ $index }}.quantity" /></td>
                                    <td class="w-24 px-3 py-2"><x-input wire:model.live.debounce.400ms="lines.{{ $index }}.unit" /></td>
                                    <td class="w-32 px-3 py-2"><x-input type="number" step="0.01" wire:model.live.debounce.300ms="lines.{{ $index }}.unit_price_ht" /></td>
                                    <td class="w-24 px-3 py-2"><x-input type="number" min="0" max="100" step="0.01" wire:model.live.debounce.300ms="lines.{{ $index }}.vat_rate" :disabled="! $vatEnabled" /></td>
                                    <td class="min-w-48 px-3 py-2"><div class="flex gap-1"><x-input type="number" min="0" step="0.01" wire:model.live="lines.{{ $index }}.discount_value" /><x-select wire:model.live="lines.{{ $index }}.discount_type"><option value="">—</option><option value="euro">€</option><option value="pourcent">%</option></x-select></div></td>
                                    <td class="whitespace-nowrap px-3 py-2 text-right font-medium">{{ number_format($lineGross - $discountAmount, 2, ',', ' ') }} €</td>
                                    <td class="px-3"><button type="button" wire:click="removeLine({{ $index }})" class="text-red-500">×</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Ajoutez au moins une ligne.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @error('lines')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror

                @php
                    $previewHt = 0;
                    $previewVat = 0;
                    foreach ($lines as $previewLine) {
                        $gross = (float) ($previewLine['quantity'] ?? 0) * (float) ($previewLine['unit_price_ht'] ?? 0);
                        $value = max(0, (float) ($previewLine['discount_value'] ?? 0));
                        $discount = ($previewLine['discount_type'] ?? '') === 'pourcent' ? $gross * min($value, 100) / 100 : (($previewLine['discount_type'] ?? '') === 'euro' ? min(max($gross, 0), $value) : 0);
                        $net = $gross - $discount;
                        $previewHt += $net;
                        $previewVat += $net * (float) ($previewLine['vat_rate'] ?? 0) / 100;
                    }
                    $globalValue = max(0, (float) ($totalDiscountValue ?: 0));
                    $globalDiscount = $totalDiscountType === 'pourcent' ? $previewHt * min($globalValue, 100) / 100 : min(max($previewHt, 0), $globalValue);
                    $ratio = $previewHt > 0 ? ($previewHt - $globalDiscount) / $previewHt : 1;
                    $previewHt -= $globalDiscount;
                    $previewVat *= $ratio;
                @endphp
                <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_420px]">
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-3 font-semibold">Remise globale</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <x-field label="Valeur"><x-input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="totalDiscountValue" placeholder="0" /></x-field>
                            <x-field label="Type"><x-select wire:model.live="totalDiscountType"><option value="euro">Montant (€)</option><option value="pourcent">Pourcentage (%)</option></x-select></x-field>
                        </div>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-4 text-sm dark:bg-gray-800/50">
                        <div class="flex justify-between py-1"><span class="text-gray-500">Total HT avant remise globale</span><span>{{ number_format($previewHt + $globalDiscount, 2, ',', ' ') }} €</span></div>
                        @if($globalDiscount > 0)
                            <div class="flex justify-between py-1 text-amber-600"><span>Remise globale</span><span>− {{ number_format($globalDiscount, 2, ',', ' ') }} €</span></div>
                        @endif
                        <div class="flex justify-between py-1"><span class="text-gray-500">Total HT</span><span>{{ number_format($previewHt, 2, ',', ' ') }} €</span></div>
                        @if($vatEnabled)
                            <div class="flex justify-between py-1"><span class="text-gray-500">TVA</span><span>{{ number_format($previewVat, 2, ',', ' ') }} €</span></div>
                        @endif
                        <div class="mt-2 flex justify-between border-t border-gray-200 pt-3 text-lg font-bold dark:border-gray-700"><span>Total TTC</span><span>{{ number_format($previewHt + $previewVat, 2, ',', ' ') }} €</span></div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <x-button type="button" variant="secondary" wire:click="$set('show', false)">Annuler</x-button>
                    <x-button type="button" wire:click="generate" wire:loading.attr="disabled" wire:confirm="Après génération, cette facture sera définitive et non modifiable. Continuer ?">Générer la facture PDF définitive</x-button>
                </div>
            </div>
        </div>
    @endif

    @if ($pdfUrl)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 p-3 md:p-6" wire:key="manual-invoice-pdf" wire:click.self="closePdf" x-on:keydown.escape.window="$wire.closePdf()">
            <div class="flex h-full max-h-[94vh] w-full max-w-6xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700"><h2 class="font-semibold">Aperçu de la facture</h2><button type="button" wire:click="closePdf" class="text-2xl text-gray-400">×</button></div>
                <iframe src="{{ $pdfUrl }}" title="Aperçu de la facture PDF" class="min-h-0 flex-1 bg-gray-100"></iframe>
            </div>
        </div>
    @endif
</div>
