<div>
    @if (! $invoiceEnabled)
        @include('livewire.partials.legacy-facturation')
    @else
    <div class="mb-4 flex justify-end"><livewire:manual-invoice /></div>
    <x-card :padding="false">
        <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 p-4 dark:border-gray-800">
            <div class="inline-flex rounded-lg border border-gray-200 p-0.5 text-sm dark:border-gray-700">
                <button wire:click="$set('filtre', 'a_facturer')" class="rounded-md px-3 py-1.5 {{ $filtre === 'a_facturer' ? 'bg-brand-600 text-white' : 'text-gray-600 dark:text-gray-300' }}">
                    À générer @if ($totalAFacturer)<span class="ml-1 rounded-full bg-white/20 px-1.5 text-xs">{{ $totalAFacturer }}</span>@endif
                </button>
                <button wire:click="$set('filtre', 'facturees')" class="rounded-md px-3 py-1.5 {{ $filtre === 'facturees' ? 'bg-brand-600 text-white' : 'text-gray-600 dark:text-gray-300' }}">Historique des factures</button>
                <button wire:click="$set('filtre', 'ignorees')" class="rounded-md px-3 py-1.5 {{ $filtre === 'ignorees' ? 'bg-brand-600 text-white' : 'text-gray-600 dark:text-gray-300' }}">Ignorées</button>
            </div>
            <div class="relative min-w-48 flex-1">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input type="search" wire:model.live.debounce.300ms="q" placeholder="N° de facture, intervention ou client…" class="w-full rounded-lg border-gray-300 pl-9 text-sm dark:border-gray-700 dark:bg-gray-800">
            </div>
        </div>

        <div class="overflow-x-auto">
            @if ($filtre === 'a_facturer')
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr>
                        <th class="px-5 py-3 font-medium">Intervention</th><th class="px-5 py-3 font-medium">Client</th><th class="px-5 py-3 font-medium">Clôturée le</th><th class="px-5 py-3 text-right font-medium">Heures</th><th class="px-5 py-3 text-right font-medium">Total HT</th><th class="px-5 py-3"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($interventions as $i)
                            <tr wire:key="pending-invoice-{{ $i->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="whitespace-nowrap px-5 py-3"><a href="{{ route('interventions.show', $i) }}" class="font-medium text-brand-600 hover:underline">{{ $i->reference }}</a></td>
                                <td class="px-5 py-3">{{ $i->client?->nomComplet() }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-400">{{ $i->closed_at?->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-right">{{ rtrim(rtrim(number_format($i->tempsTotal(), 2), '0'), '.') }} h</td>
                                <td class="px-5 py-3 text-right font-medium">{{ number_format((float) ($i->montant_total ?? 0), 2, ',', ' ') }} €</td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" wire:click="ignore({{ $i->id }})" wire:confirm="Ignorer cette intervention de la facturation ?" class="text-sm text-gray-500 hover:text-amber-600">Ignorer</button>
                                        <x-button type="button" wire:click="$dispatch('open-invoice-editor', { interventionId: {{ $i->id }} })">Préparer la facture</x-button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state icon="check" title="Aucune facture à générer" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif ($filtre === 'facturees')
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr>
                        <th class="px-5 py-3 font-medium">N° facture</th><th class="px-5 py-3 font-medium">Date</th><th class="px-5 py-3 font-medium">Client</th><th class="px-5 py-3 font-medium">Intervention</th><th class="px-5 py-3 font-medium">Paiement</th><th class="px-5 py-3 text-right font-medium">Total</th><th class="px-5 py-3"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($invoices as $invoice)
                            <tr wire:key="invoice-{{ $invoice->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="whitespace-nowrap px-5 py-3 font-semibold">{{ $invoice->number }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-500">{{ $invoice->issued_at->format('d/m/Y') }}</td>
                                <td class="px-5 py-3">{{ $invoice->customer['name'] ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    @if ($invoice->intervention)
                                        <a href="{{ route('interventions.show', $invoice->intervention) }}" class="text-brand-600 hover:underline">{{ $invoice->intervention->reference }}</a>
                                    @else
                                        <span class="text-gray-400">Facture libre</span>
                                    @endif
                                </td>
                                @php($paymentStatus = $invoice->paymentStatus())
                                <td class="px-5 py-3"><span class="rounded-full px-2 py-1 text-xs font-medium {{ $paymentStatus === 'paid' ? 'bg-green-100 text-green-700' : ($paymentStatus === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">{{ $invoice->paymentStatusLabel() }}</span><div class="mt-1 text-xs text-gray-400">Reste {{ number_format($invoice->balanceDue(), 2, ',', ' ') }} €</div></td>
                                <td class="px-5 py-3 text-right font-medium">{{ number_format($invoice->vat_enabled ? $invoice->total_ttc : $invoice->total_ht, 2, ',', ' ') }} € {{ $invoice->vat_enabled ? 'TTC' : 'HT' }}</td>
                                <td class="px-5 py-3 text-right"><button type="button" wire:click="openPdf({{ $invoice->id }})" class="font-medium text-brand-600 hover:underline">Ouvrir la facture</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><x-empty-state icon="list" title="Aucune facture générée" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr>
                        <th class="px-5 py-3 font-medium">Intervention</th><th class="px-5 py-3 font-medium">Client</th><th class="px-5 py-3 font-medium">Ignorée le</th><th class="px-5 py-3 font-medium">Par</th><th class="px-5 py-3"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($interventions as $i)
                            <tr wire:key="ignored-invoice-{{ $i->id }}">
                                <td class="px-5 py-3"><a href="{{ route('interventions.show', $i) }}" class="font-medium text-brand-600 hover:underline">{{ $i->reference }}</a></td>
                                <td class="px-5 py-3">{{ $i->client?->nomComplet() }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $i->invoice_ignored_at?->format('d/m/Y à H:i') }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $i->invoiceIgnoredBy?->fullName() ?: '—' }}</td>
                                <td class="px-5 py-3 text-right"><button type="button" wire:click="restoreIgnored({{ $i->id }})" class="font-medium text-brand-600 hover:underline">Réintégrer</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty-state icon="check" title="Aucune intervention ignorée" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        @php($paginator = $filtre === 'facturees' ? $invoices : $interventions)
        @if ($paginator->hasPages())<div class="border-t border-gray-100 p-4 dark:border-gray-800">{{ $paginator->links() }}</div>@endif
    </x-card>

    @if ($pdfUrl)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 p-3 md:p-6" wire:key="invoice-pdf-modal" wire:click.self="closePdf" x-on:keydown.escape.window="$wire.closePdf()">
            <div class="flex h-full max-h-[94vh] w-full max-w-[95rem] flex-col overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <h2 class="font-semibold">Aperçu de la facture</h2>
                    <div class="flex items-center gap-3">
                        <a href="{{ $pdfUrl }}" download class="text-sm font-medium text-brand-600 hover:underline">Télécharger</a>
                        <button type="button" wire:click="closePdf" class="rounded-lg px-3 py-1.5 text-xl leading-none text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="Fermer">×</button>
                    </div>
                </div>
                <div class="flex min-h-0 flex-1"><iframe src="{{ $pdfUrl }}" title="Aperçu de la facture PDF" class="min-h-0 flex-1 bg-gray-100"></iframe>@if($selectedInvoiceId)<aside class="w-[380px] shrink-0 overflow-y-auto border-l border-gray-200 dark:border-gray-700"><livewire:invoice-payments :invoice="\App\Models\Invoice::findOrFail($selectedInvoiceId)" :key="'payments-'.$selectedInvoiceId" /></aside>@endif</div>
            </div>
        </div>
    @endif
    @endif
</div>
