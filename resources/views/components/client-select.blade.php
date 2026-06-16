@props([
    'name' => 'client_id',
    'selected' => null,
    'selectedLabel' => '',
])

<div {{ $attributes->only('class') }}
     x-data="clientSelect({
        selected: @js($selected),
        selectedLabel: @js($selectedLabel),
        searchUrl: '{{ route('clients.search') }}',
        createUrl: '{{ route('clients.quick-store') }}',
     })"
     @keydown.escape="open = false" class="relative">
    <input type="hidden" name="{{ $name }}" x-ref="input" :value="value">

    <button type="button" @click="toggle()"
            class="flex w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
        <span x-text="label || 'Rechercher un client…'" :class="value ? '' : 'text-gray-400'" class="truncate"></span>
        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false" x-transition.opacity
         class="absolute z-30 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 p-2 dark:border-gray-700">
            <input x-ref="search" x-model="query" @input.debounce.300ms="search()" type="text"
                   placeholder="Nom, e-mail, ville… (2 caractères min.)"
                   class="w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-900">
        </div>
        <ul class="max-h-60 overflow-y-auto py-1 text-sm">
            <li x-show="loading" class="px-3 py-2 text-gray-400">Recherche…</li>
            <template x-for="c in results" :key="c.id">
                <li @click="pick(c)" class="cursor-pointer px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <span x-text="c.label" class="font-medium"></span>
                    <span x-show="c.ville" x-text="' · ' + c.ville" class="text-xs text-gray-400"></span>
                </li>
            </template>
            <li x-show="!loading && query.length >= 2 && results.length === 0" class="px-3 py-2 text-gray-400">
                Aucun client trouvé.
            </li>
        </ul>
        {{-- Inline creation --}}
        <div class="border-t border-gray-100 p-2 dark:border-gray-700">
            <div class="flex gap-2">
                <input x-model="newName" type="text" placeholder="Créer un client : nom…"
                       class="w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-900">
                <button type="button" @click="create()" :disabled="creating"
                        class="shrink-0 rounded-md bg-brand-600 px-3 text-sm font-medium text-white hover:bg-brand-700 disabled:opacity-50">
                    <span x-show="!creating">Créer</span><span x-show="creating">…</span>
                </button>
            </div>
        </div>
    </div>
</div>
