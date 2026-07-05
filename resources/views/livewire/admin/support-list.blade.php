<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight">Assistance</h1>
        <p class="mt-1 text-sm text-gray-500">Toutes les demandes d'assistance, toutes sociétés confondues.</p>
    </div>

    {{-- Compteurs par statut --}}
    <div class="mb-6 flex flex-wrap gap-2">
        @php $total = $counts->sum(); @endphp
        <button wire:click="$set('statut', '')"
                class="rounded-lg border px-3 py-1.5 text-sm {{ $statut === '' ? 'border-brand-600 bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300' : 'border-gray-200 dark:border-gray-800' }}">
            Toutes <span class="font-semibold">{{ $total }}</span>
        </button>
        @foreach (\App\Models\SupportTicket::STATUTS as $key => $label)
            <button wire:click="$set('statut', '{{ $key }}')"
                    class="rounded-lg border px-3 py-1.5 text-sm {{ $statut === $key ? 'border-brand-600 bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300' : 'border-gray-200 dark:border-gray-800' }}">
                {{ $label }} <span class="font-semibold">{{ $counts[$key] ?? 0 }}</span>
            </button>
        @endforeach
    </div>

    <div class="mb-4">
        <input type="search" wire:model.live.debounce.300ms="q" placeholder="Rechercher un sujet ou une société…"
               class="w-full max-w-md rounded-lg border-gray-300 bg-gray-50 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800" />
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                <tr>
                    <th class="px-5 py-3">Réf.</th>
                    <th class="px-5 py-3">Sujet</th>
                    <th class="px-5 py-3">Société</th>
                    <th class="px-5 py-3">Demandeur</th>
                    <th class="px-5 py-3">Type</th>
                    <th class="px-5 py-3">Urgence</th>
                    <th class="px-5 py-3">Statut</th>
                    <th class="px-5 py-3">Activité</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($tickets as $ticket)
                    <tr wire:key="ticket-{{ $ticket->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-gray-500">{{ $ticket->reference }}</td>
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.support.show', $ticket) }}" class="font-medium text-gray-900 hover:text-brand-600 dark:text-gray-100">{{ $ticket->sujet }}</a>
                            @if ($ticket->messages_count > 0)<span class="ml-1 text-xs text-gray-400">({{ $ticket->messages_count }})</span>@endif
                        </td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $ticket->society?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $ticket->user?->fullName() ?: '—' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $ticket->typeLabel() }}</td>
                        <td class="px-5 py-3"><x-badge :color="$ticket->urgenceColor()">{{ $ticket->urgenceLabel() }}</x-badge></td>
                        <td class="px-5 py-3"><x-badge :color="$ticket->statutColor()">{{ $ticket->statutLabel() }}</x-badge></td>
                        <td class="whitespace-nowrap px-5 py-3 text-xs text-gray-400">{{ ($ticket->last_reply_at ?? $ticket->created_at)->diffForHumans() }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.support.show', $ticket) }}" class="text-xs font-medium text-brand-600 hover:underline">Traiter →</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-5 py-10 text-center text-gray-400">Aucune demande d'assistance.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>

    @if ($tickets->hasPages())<div class="mt-4">{{ $tickets->links() }}</div>@endif
</div>
