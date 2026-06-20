<div>
    <x-card :padding="false">
        <div class="border-b border-gray-100 p-4 dark:border-gray-800">
            <div class="relative max-w-md">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <x-input wire:model.live.debounce.300ms="q" placeholder="Réf., client, fournisseur, n° commande, suivi…" class="pl-9" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-5 py-3 font-medium">Intervention</th>
                        <th class="px-5 py-3 font-medium">Client</th>
                        <th class="px-5 py-3 font-medium">Fournisseur / commande</th>
                        <th class="px-5 py-3 font-medium">Suivi colis</th>
                        <th class="px-5 py-3 font-medium">Technicien(s)</th>
                        <th class="px-5 py-3 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($commandes as $c)
                        <tr wire:key="cmd-{{ $c->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="whitespace-nowrap px-5 py-3">
                                <a href="{{ route('interventions.show', $c->intervention) }}" class="font-medium text-brand-600 hover:underline">{{ $c->intervention->reference }}</a>
                            </td>
                            <td class="px-5 py-3">{{ $c->intervention->client?->nomComplet() ?: '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="font-medium">{{ $c->fournisseur ?: 'Commande' }}</span>
                                @if ($c->numero_commande)<span class="text-gray-400"> · {{ $c->numero_commande }}</span>@endif
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $c->suivi_colis ?: '—' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex -space-x-1.5">
                                    @forelse ($c->intervention->techniciens->take(3) as $t)
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-[10px] font-semibold text-white ring-2 ring-white dark:ring-gray-900" title="{{ $t->fullName() }}">{{ $t->initials() }}</span>
                                    @empty
                                        <span class="text-xs text-gray-400">Aucun</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <x-button type="button" wire:click="receive({{ $c->id }})" wire:confirm="Marquer cette commande comme reçue ? Le technicien sera notifié.">Marquer reçue</x-button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="check" title="Aucune commande en attente" message="Toutes les commandes fournisseurs ont été réceptionnées." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($commandes->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">{{ $commandes->links() }}</div>
        @endif
    </x-card>
</div>
