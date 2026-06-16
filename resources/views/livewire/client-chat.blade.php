<div wire:poll.10s class="flex flex-col" x-data x-init="$nextTick(() => $refs.feed && ($refs.feed.scrollTop = $refs.feed.scrollHeight))">
    <div x-ref="feed" class="max-h-80 space-y-3 overflow-y-auto px-1 py-2">
        @forelse ($messages as $m)
            @php $mine = $m->author === $author; @endphp
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] rounded-2xl px-3 py-2 text-sm
                    {{ $mine ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100' }}">
                    <p class="mb-0.5 text-[11px] opacity-70">
                        {{ $m->author === 'client' ? 'Client' : ($m->user?->prenom ?? 'Technicien') }} · {{ $m->created_at?->format('d/m H:i') }}
                    </p>
                    <p class="whitespace-pre-line">{{ $m->message }}</p>
                </div>
            </div>
        @empty
            <p class="py-6 text-center text-sm text-gray-400">Aucun message pour le moment. Écrivez-nous ci-dessous.</p>
        @endforelse
    </div>

    <form wire:submit="send" class="mt-3 flex gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
        <input type="text" wire:model="body" placeholder="Votre message…"
               class="flex-1 rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
        <button type="submit" wire:loading.attr="disabled"
                class="rounded-lg bg-brand-600 px-4 text-sm font-medium text-white hover:bg-brand-700 disabled:opacity-50">
            Envoyer
        </button>
    </form>
    @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
