<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight">Assistance</h1>
        <p class="mt-1 text-sm text-gray-500">Toutes les demandes d'assistance, toutes sociétés confondues.</p>
    </div>

    {{-- Filtres rapides par statut --}}
    <div class="mb-4 flex flex-wrap gap-2">
        @php $total = $counts->sum(); @endphp
        <button wire:click="$set('statut', 'non_clotures')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $statut === 'non_clotures' ? 'border-brand-600 bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300' : 'border-gray-200 dark:border-gray-800' }}">
            Non clôturés <span class="font-semibold">{{ $openCount }}</span>
        </button>
        <button wire:click="$set('statut', '')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $statut === '' ? 'border-brand-600 bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300' : 'border-gray-200 dark:border-gray-800' }}">
            Tous <span class="font-semibold">{{ $total }}</span>
        </button>
        @foreach (\App\Models\SupportTicket::STATUTS as $key => $label)
            <button wire:click="$set('statut', '{{ $key }}')"
                    class="rounded-lg border px-3 py-1.5 text-sm {{ $statut === $key ? 'border-brand-600 bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300' : 'border-gray-200 dark:border-gray-800' }}">
                {{ $label }} <span class="font-semibold">{{ $counts[$key] ?? 0 }}</span>
            </button>
        @endforeach
    </div>

    {{-- Recherche + filtres détaillés --}}
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="relative min-w-[16rem] flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
            <input type="search" wire:model.live.debounce.300ms="q" placeholder="Rechercher (sujet, société, demandeur, réf.)…"
                   class="w-full rounded-lg border-gray-300 bg-gray-50 pl-9 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800" />
        </div>
        <x-select wire:model.live="type" class="w-40">
            <option value="">Tous les types</option>
            @foreach (\App\Models\SupportTicket::TYPES as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-select>
        <x-select wire:model.live="urgence" class="w-40">
            <option value="">Toutes urgences</option>
            @foreach (\App\Models\SupportTicket::URGENCES as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-select>
        <x-select wire:model.live="societyId" class="w-48">
            <option value="">Toutes les sociétés</option>
            @foreach ($societies as $society)
                <option value="{{ $society->id }}">{{ $society->name }}</option>
            @endforeach
        </x-select>
        @if ($statut !== 'non_clotures' || $type || $urgence || $societyId || $q)
            <button wire:click="resetFilters" class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-800">
                Réinitialiser
            </button>
        @endif
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                <tr>
                    @foreach ([['reference', 'Réf.'], ['sujet', 'Sujet'], ['societe', 'Société'], ['demandeur', 'Demandeur'], ['type', 'Type'], ['urgence', 'Urgence'], ['statut', 'Statut'], ['activite', 'Modifié'], ['cree', 'Créé']] as [$field, $label])
                        <th class="px-5 py-3">
                            <button wire:click="sortBy('{{ $field }}')" class="group inline-flex items-center gap-1 font-semibold uppercase tracking-wide hover:text-gray-700 dark:hover:text-gray-200">
                                {{ $label }}
                                @if ($sort === $field)
                                    <span class="text-brand-600">{{ $dir === 'asc' ? '▲' : '▼' }}</span>
                                @else
                                    <span class="text-gray-300 opacity-0 group-hover:opacity-100 dark:text-gray-600">↕</span>
                                @endif
                            </button>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($tickets as $ticket)
                    <tr wire:key="ticket-{{ $ticket->id }}"
                        onclick="window.location='{{ route('admin.support.show', $ticket) }}'"
                        class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-500">{{ $ticket->reference }}</td>
                        <td class="px-5 py-3">
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $ticket->sujet }}</span>
                            @if ($ticket->messages_count > 0)<span class="ml-1 text-xs text-gray-400">({{ $ticket->messages_count }})</span>@endif
                        </td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $ticket->society?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $ticket->user?->fullName() ?: '—' }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-gray-500">{{ $ticket->typeLabel() }}</td>
                        <td class="px-5 py-3"><x-badge :color="$ticket->urgenceColor()">{{ $ticket->urgenceLabel() }}</x-badge></td>
                        <td class="px-5 py-3"><x-badge :color="$ticket->statutColor()">{{ $ticket->statutLabel() }}</x-badge></td>
                        <td class="whitespace-nowrap px-5 py-3 text-xs text-gray-400">{{ ($ticket->last_reply_at ?? $ticket->created_at)->diffForHumans() }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-xs text-gray-400">{{ $ticket->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-5 py-10 text-center text-gray-400">Aucune demande ne correspond à ces critères.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>

    @if ($tickets->hasPages())<div class="mt-4">{{ $tickets->links() }}</div>@endif
</div>
