<div>
    <x-card :padding="false">
        <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 p-4 dark:border-gray-800">
            <div class="inline-flex rounded-lg border border-gray-200 p-0.5 text-sm dark:border-gray-700">
                <button wire:click="$set('filtre', 'a_facturer')"
                        class="rounded-md px-3 py-1.5 {{ $filtre === 'a_facturer' ? 'bg-brand-600 text-white' : 'text-gray-600 dark:text-gray-300' }}">
                    À facturer
                    @if ($totalAFacturer)<span class="ml-1 rounded-full bg-white/20 px-1.5 text-xs">{{ $totalAFacturer }}</span>@endif
                </button>
                <button wire:click="$set('filtre', 'facturees')"
                        class="rounded-md px-3 py-1.5 {{ $filtre === 'facturees' ? 'bg-brand-600 text-white' : 'text-gray-600 dark:text-gray-300' }}">
                    Facturées
                </button>
            </div>

            <div class="relative flex-1 min-w-48">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input type="search" wire:model.live.debounce.300ms="q" placeholder="Référence ou client…"
                       class="w-full rounded-lg border-gray-300 pl-9 text-sm dark:border-gray-700 dark:bg-gray-800">
            </div>

            @if ($filtre === 'a_facturer' && $totalAFacturer)
                <x-button wire:click="facturerTout" wire:confirm="Marquer toutes les interventions à facturer comme facturées ?">
                    Tout facturer ({{ $totalAFacturer }})
                </x-button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-5 py-3 font-medium">Réf.</th>
                        <th class="px-5 py-3 font-medium">Client</th>
                        <th class="px-5 py-3 font-medium">Clôturée le</th>
                        <th class="px-5 py-3 text-right font-medium">Heures</th>
                        <th class="px-5 py-3 text-right font-medium">Tarif est.</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($interventions as $i)
                        <tr wire:key="fact-{{ $i->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="whitespace-nowrap px-5 py-3">
                                <a href="{{ route('interventions.show', $i) }}" class="font-medium text-brand-600 hover:underline">{{ $i->reference }}</a>
                            </td>
                            <td class="px-5 py-3">{{ $i->client?->nomComplet() }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-400">{{ $i->closed_at?->format('d/m/Y') }}</td>
                            <td class="px-5 py-3 text-right">{{ rtrim(rtrim(number_format($i->tempsTotal(), 2), '0'), '.') }} h</td>
                            <td class="px-5 py-3 text-right">{{ $i->tarif_estimatif ? number_format($i->tarif_estimatif, 2, ',', ' ').' €' : '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                @if ($filtre === 'a_facturer')
                                    <x-button wire:click="facturer({{ $i->id }})" wire:loading.attr="disabled">Marquer facturée</x-button>
                                @else
                                    <button wire:click="annuler({{ $i->id }})" class="text-sm text-gray-500 hover:text-red-600">Retirer</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="check" :title="$filtre === 'a_facturer' ? 'Rien à facturer 🎉' : 'Aucune intervention facturée'" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($interventions->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">{{ $interventions->links() }}</div>
        @endif
    </x-card>
</div>
