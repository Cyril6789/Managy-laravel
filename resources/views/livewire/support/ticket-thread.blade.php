<div class="mx-auto max-w-3xl">
    <a href="{{ route('support.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-600">
        &larr; Retour aux demandes
    </a>

    {{-- En-tête du ticket --}}
    <x-card class="mb-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-bold tracking-tight">{{ $ticket->sujet }}</h1>
                    <x-badge :color="$ticket->statutColor()">{{ $ticket->statutLabel() }}</x-badge>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $ticket->reference }} · {{ $ticket->typeLabel() }} ·
                    <x-badge :color="$ticket->urgenceColor()">Urgence {{ $ticket->urgenceLabel() }}</x-badge>
                </p>
                <p class="mt-2 text-xs text-gray-400">
                    Demandeur : <span class="font-medium text-gray-600 dark:text-gray-300">{{ $ticket->user?->fullName() ?: 'Utilisateur supprimé' }}</span>
                    · Ouvert {{ $ticket->created_at->diffForHumans() }}
                </p>
            </div>

            @if ($ticket->user_id === auth()->id() || auth()->user()->is_admin)
                <div class="shrink-0">
                    @if ($ticket->statut === 'ferme')
                        <x-button variant="secondary" wire:click="reopen">Ré-ouvrir</x-button>
                    @else
                        <x-button variant="secondary" wire:click="close" wire:confirm="Fermer cette demande ?">Fermer la demande</x-button>
                    @endif
                </div>
            @endif
        </div>

        <div class="mt-4 whitespace-pre-line rounded-lg bg-gray-50 p-4 text-sm text-gray-700 dark:bg-gray-800/50 dark:text-gray-200">{{ $ticket->description }}</div>
    </x-card>

    {{-- Fil de discussion --}}
    <div class="space-y-3">
        @foreach ($messages as $message)
            <div wire:key="msg-{{ $message->id }}"
                 class="rounded-xl border p-4 {{ $message->is_staff
                     ? 'border-brand-200 bg-brand-50/60 dark:border-brand-900 dark:bg-brand-900/20'
                     : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900' }}">
                <div class="mb-1 flex items-center gap-2 text-sm">
                    <span class="font-semibold">{{ $message->author?->fullName() ?: 'Utilisateur supprimé' }}</span>
                    @if ($message->is_staff)
                        <span class="rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">Support</span>
                    @endif
                    <span class="text-xs text-gray-400">{{ $message->created_at->diffForHumans() }}</span>
                </div>
                <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-200">{{ $message->body }}</p>
            </div>
        @endforeach
    </div>

    {{-- Répondre --}}
    @if ($ticket->statut !== 'ferme')
        <form wire:submit="reply" class="mt-6">
            <x-field label="Votre réponse">
                <x-textarea wire:model="body" rows="3" placeholder="Ajouter un message au support…" />
                @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </x-field>
            <div class="mt-2 flex justify-end">
                <x-button type="submit" wire:loading.attr="disabled" wire:target="reply">Envoyer</x-button>
            </div>
        </form>
    @else
        <p class="mt-6 text-center text-sm text-gray-400">Cette demande est fermée. Ré-ouvrez-la pour poursuivre l'échange.</p>
    @endif
</div>
