<div class="space-y-4 p-4 text-sm">
    @php($status = $invoice->paymentStatus())
    <div class="rounded-xl p-4 {{ $status === 'paid' ? 'bg-green-50 text-green-800 dark:bg-green-950/30 dark:text-green-300' : ($status === 'partial' ? 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200') }}">
        <div class="font-semibold">{{ $invoice->paymentStatusLabel() }}</div>
        <div class="mt-2 flex justify-between"><span>Réglé</span><strong>{{ number_format($invoice->paidAmount(), 2, ',', ' ') }} €</strong></div>
        <div class="flex justify-between"><span>Reste à payer</span><strong>{{ number_format($invoice->balanceDue(), 2, ',', ' ') }} €</strong></div>
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="button" wire:click="$dispatch('invoice-pdf-selected', { url: @js(route('invoices.pdf', $invoice)) })" class="text-brand-600 underline">PDF original</button>
        @if($latest = $payments->last())
            <button type="button" wire:click="$dispatch('invoice-pdf-selected', { url: @js(route('invoices.payment-state', [$invoice, $latest])) })" class="text-brand-600 underline">Dernier état de paiement</button>
        @endif
    </div>

    @if($status !== 'paid')
        <form wire:submit="record" class="space-y-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
            <h3 class="font-semibold">Enregistrer un règlement</h3>
            <div class="grid grid-cols-2 gap-3">
                <x-field label="Montant (€)"><x-input type="number" min="0.01" step="0.01" wire:model="form.amount" /></x-field>
                <x-field label="Date"><x-input type="datetime-local" wire:model="form.paid_at" /></x-field>
            </div>
            <x-field label="Mode"><x-select wire:model="form.method"><option value="cb">Carte bancaire</option><option value="especes">Espèces</option><option value="cheque">Chèque</option><option value="virement">Virement</option><option value="autre">Autre</option></x-select></x-field>
            <x-field label="Référence"><x-input wire:model="form.reference" placeholder="N° transaction, chèque…" /></x-field>
            <x-field label="Note"><x-textarea rows="2" wire:model="form.note" /></x-field>
            @error('form.amount')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            <x-button type="submit" wire:loading.attr="disabled">Valider le règlement</x-button>
        </form>
    @endif

    <div>
        <h3 class="mb-2 font-semibold">Historique des règlements</h3>
        @forelse($payments as $payment)
            <div class="border-t border-gray-100 py-3 dark:border-gray-800">
                <div class="flex justify-between"><strong>{{ number_format($payment->amount, 2, ',', ' ') }} €</strong><span>{{ $payment->paid_at->format('d/m/Y H:i') }}</span></div>
                <div class="text-xs text-gray-500">{{ $payment->methodLabel() }}{{ $payment->reference ? ' · '.$payment->reference : '' }}</div>
                @if($payment->note)<p class="mt-1 text-xs">{{ $payment->note }}</p>@endif
                <a href="{{ route('invoices.payment-state', [$invoice, $payment]) }}" class="text-xs text-brand-600 underline" download>Télécharger cet état</a>
            </div>
        @empty
            <p class="text-gray-400">Aucun règlement enregistré.</p>
        @endforelse
    </div>
</div>
