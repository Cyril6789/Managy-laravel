<div>
    <x-button type="button" wire:click="open">+ Nouvelle facture</x-button>

    @if ($show)
        <div class="fixed inset-0 z-[90] flex items-start justify-center overflow-y-auto bg-black/60 p-4 py-8" wire:key="manual-invoice-modal" wire:click.self="$set('show', false)" x-on:keydown.escape.window="$wire.set('show', false)">
            <div class="w-full max-w-5xl rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <div><h2 class="text-lg font-semibold">Nouvelle facture</h2><p class="text-sm text-gray-500">Facture libre, sans intervention associée.</p></div>
                    <button type="button" wire:click="$set('show', false)" class="text-2xl text-gray-400">×</button>
                </div>

                <x-field label="Client" required>
                    <x-searchable-select name="manual_invoice_client" wire:model.change="clientId" :selected="$clientId"
                        :options="$clients->mapWithKeys(fn ($client) => [$client->id => $client->nomComplet()])"
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
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800"><tr><th class="px-4 py-2 text-left">Désignation</th><th class="px-4 py-2 text-right">Qté</th><th class="px-4 py-2 text-right">PU HT</th><th class="px-4 py-2 text-right">TVA</th><th class="px-4 py-2 text-right">Total TTC</th><th></th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($lines as $index => $line)
                                @php($lineHt = $line['quantity'] * $line['unit_price_ht'])
                                @php($lineTtc = $lineHt * (1 + $line['vat_rate'] / 100))
                                <tr wire:key="manual-line-{{ $index }}"><td class="px-4 py-2">{{ $line['description'] }}</td><td class="px-4 py-2 text-right">{{ $line['quantity'] }} {{ $line['unit'] }}</td><td class="px-4 py-2 text-right">{{ number_format($line['unit_price_ht'], 2, ',', ' ') }} €</td><td class="px-4 py-2 text-right">{{ number_format($line['vat_rate'], 2, ',', ' ') }} %</td><td class="px-4 py-2 text-right font-medium">{{ number_format($lineTtc, 2, ',', ' ') }} €</td><td class="px-3"><button type="button" wire:click="removeLine({{ $index }})" class="text-red-500">×</button></td></tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Ajoutez au moins une ligne.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @error('lines')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror

                <div class="mt-6 flex justify-end gap-2">
                    <x-button type="button" variant="secondary" wire:click="$set('show', false)">Annuler</x-button>
                    <x-button type="button" wire:click="generate" wire:loading.attr="disabled">Générer la facture PDF</x-button>
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
