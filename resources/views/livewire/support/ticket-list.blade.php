<div>
    <x-page-header title="Assistance" subtitle="Posez une question, déclarez un bug ou proposez une amélioration. Le support suit chaque demande.">
        <x-slot:actions>
            <x-button wire:click="$set('showModal', true)"><x-icon name="plus" class="h-4 w-4" /> Nouvelle demande</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card :padding="false">
        <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 p-4 dark:border-gray-800">
            <x-select wire:model.live="statut" class="w-64">
                <option value="">Tous les statuts</option>
                @foreach (\App\Models\SupportTicket::STATUTS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-select>
            <span class="text-sm text-gray-500">{{ $openCount }} demande(s) en cours</span>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($tickets as $ticket)
                <a href="{{ route('support.show', $ticket) }}" wire:navigate wire:key="ticket-{{ $ticket->id }}"
                   class="flex items-start gap-3 px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800">
                        <x-icon :name="$ticket->typeIcon()" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-medium">{{ $ticket->sujet }}</span>
                            <x-badge :color="$ticket->statutColor()">{{ $ticket->statutLabel() }}</x-badge>
                        </div>
                        <p class="mt-0.5 text-xs text-gray-400">
                            {{ $ticket->reference }} · {{ $ticket->typeLabel() }} ·
                            Ouvert par {{ $ticket->user?->fullName() ?: 'Utilisateur supprimé' }} ·
                            {{ $ticket->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="hidden shrink-0 flex-col items-end gap-1 sm:flex">
                        <x-badge :color="$ticket->urgenceColor()">{{ $ticket->urgenceLabel() }}</x-badge>
                        @if ($ticket->messages_count > 0)
                            <span class="text-xs text-gray-400">{{ $ticket->messages_count }} message(s)</span>
                        @endif
                    </div>
                </a>
            @empty
                <x-empty-state icon="lifebuoy" title="Aucune demande" message="Aucune demande d'assistance pour le moment. Créez-en une pour poser une question ou signaler un problème." />
            @endforelse
        </div>

        @if ($tickets->hasPages())<div class="border-t border-gray-100 p-4 dark:border-gray-800">{{ $tickets->links() }}</div>@endif
    </x-card>

    {{-- Modale : nouvelle demande --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:key="support-modal"
             @keydown.escape.window="$wire.set('showModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold">Nouvelle demande d'assistance</h3>
                <form wire:submit="create" class="space-y-4">
                    <x-field label="Sujet" required>
                        <x-input wire:model="form.sujet" placeholder="Résumé de votre demande" />
                        @error('form.sujet')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </x-field>
                    <div class="grid grid-cols-2 gap-3">
                        <x-field label="Type" required>
                            <x-select wire:model="form.type">
                                @foreach (\App\Models\SupportTicket::TYPES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </x-select>
                        </x-field>
                        <x-field label="Niveau d'urgence" required>
                            <x-select wire:model="form.urgence">
                                @foreach (\App\Models\SupportTicket::URGENCES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </x-select>
                        </x-field>
                    </div>
                    <x-field label="Description" required>
                        <x-textarea wire:model="form.description" rows="5" placeholder="Décrivez votre question, le bug rencontré ou votre suggestion…" />
                        @error('form.description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </x-field>
                    <div class="flex justify-end gap-2">
                        <x-button type="button" variant="secondary" wire:click="$set('showModal', false)">Annuler</x-button>
                        <x-button type="submit" wire:loading.attr="disabled" wire:target="create">Envoyer la demande</x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
