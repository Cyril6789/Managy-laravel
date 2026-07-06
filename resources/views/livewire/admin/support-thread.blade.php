<div class="mx-auto max-w-4xl">
    <a href="{{ route('admin.support.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-brand-600">
        &larr; Retour à l'assistance
    </a>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Conversation --}}
        <div class="lg:col-span-2">
            <x-card class="mb-6">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-bold tracking-tight">{{ $ticket->sujet }}</h1>
                    <x-badge :color="$ticket->statutColor()">{{ $ticket->statutLabel() }}</x-badge>
                </div>
                <p class="mt-1 text-sm text-gray-500">{{ $ticket->reference }} · {{ $ticket->typeLabel() }}</p>
                <div class="mt-4 whitespace-pre-line rounded-lg bg-gray-50 p-4 text-sm text-gray-700 dark:bg-gray-800/50 dark:text-gray-200">{{ $ticket->description }}</div>
            </x-card>

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

            <form wire:submit="reply" class="mt-6">
                <x-field label="Répondre au demandeur">
                    <x-textarea wire:model="body" rows="4" placeholder="Votre réponse en tant que support…" />
                    @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </x-field>
                <div class="mt-2 flex justify-end">
                    <x-button type="submit" wire:loading.attr="disabled" wire:target="reply">Envoyer la réponse</x-button>
                </div>
            </form>
        </div>

        {{-- Panneau latéral : demandeur, société, traitement --}}
        <div class="space-y-4">
            <x-card title="Demande">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-2"><dt class="text-gray-500">Société</dt><dd class="text-right font-medium">{{ $ticket->society?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-gray-500">Demandeur</dt><dd class="text-right font-medium">{{ $ticket->user?->fullName() ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-gray-500">Email</dt><dd class="text-right text-gray-600 dark:text-gray-300">{{ $ticket->user?->email ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-gray-500">Type</dt><dd class="text-right">{{ $ticket->typeLabel() }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-gray-500">Ouvert le</dt><dd class="text-right text-gray-600 dark:text-gray-300">{{ $ticket->created_at->format('d/m/Y H:i') }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Traitement">
                <form wire:submit="updateStatus" class="space-y-3">
                    <x-field label="Statut">
                        <x-select wire:model="statut">
                            @foreach (\App\Models\SupportTicket::STATUTS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </x-select>
                    </x-field>
                    <x-field label="Urgence">
                        <x-select wire:model="urgence">
                            @foreach (\App\Models\SupportTicket::URGENCES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </x-select>
                    </x-field>
                    <x-button type="submit" class="w-full" wire:loading.attr="disabled" wire:target="updateStatus">Mettre à jour</x-button>
                </form>
            </x-card>
        </div>
    </div>
</div>
