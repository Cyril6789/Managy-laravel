<div class="md:col-span-2 mt-2 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
    <p class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Sociétés rattachées</p>
    <p class="mb-3 text-xs text-gray-400">Ce particulier peut être le contact d'une ou plusieurs sociétés, tout en gardant ses propres interventions.</p>

    <div class="flex flex-wrap gap-2">
        @forelse ($companies as $c)
            <span wire:key="company-{{ $c->id }}" class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-3 py-1 text-sm text-brand-700 dark:bg-brand-600/20 dark:text-brand-300">
                <a href="{{ route('clients.show', $c) }}" class="hover:underline">{{ $c->nomComplet() }}</a>
                <button type="button" wire:click="detach({{ $c->id }})" class="text-brand-400 hover:text-red-600" title="Détacher">&times;</button>
            </span>
        @empty
            <span class="text-sm text-gray-400">Aucune société rattachée.</span>
        @endforelse
    </div>

    <div class="relative mt-3" @click.outside="$wire.set('open', false)">
        <input type="text" wire:model.live.debounce.300ms="query" placeholder="Rechercher une société… (2 caractères min.)"
               class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
        @if ($open && (strlen(trim($query)) >= 2))
            <ul class="absolute z-30 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 text-sm shadow-lg dark:border-gray-700 dark:bg-gray-800">
                @forelse ($results as $r)
                    <li wire:key="res-{{ $r['id'] }}" wire:click="attach({{ $r['id'] }})"
                        class="cursor-pointer px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <span class="font-medium">{{ $r['label'] }}</span>
                        @if ($r['ville'])<span class="text-xs text-gray-400"> · {{ $r['ville'] }}</span>@endif
                    </li>
                @empty
                    <li class="px-3 py-2 text-gray-400">Aucune société trouvée.</li>
                @endforelse
            </ul>
        @endif
    </div>
</div>
