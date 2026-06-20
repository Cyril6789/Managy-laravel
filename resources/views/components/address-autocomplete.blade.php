@props([
    'label' => "Recherche d'adresse",
    'placeholder' => 'Tapez une adresse, un code postal, une ville…',
])

{{--
    Address search box (Base Adresse Nationale). Emits an `address-picked` DOM
    event (detail = { adresse, code_postal, ville, label }) that bubbles up to
    the surrounding form, which is responsible for filling its own fields.
--}}
<div x-data="addressAutocomplete({ url: '{{ route('adresse.search') }}' })" class="relative" @keydown.escape="open = false">
    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ $label }}
    </label>
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z"/></svg>
        </span>
        <input type="text" x-model="query" autocomplete="off"
               @input.debounce.300ms="search()"
               @focus="if (results.length) open = true"
               @keydown.arrow-down.prevent="move(1)"
               @keydown.arrow-up.prevent="move(-1)"
               @keydown.enter.prevent="enter()"
               placeholder="{{ $placeholder }}"
               class="block w-full rounded-lg border-gray-300 pl-9 pr-9 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
        <span x-show="loading" x-cloak class="absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="h-4 w-4 animate-spin text-brand-500" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"/></svg>
        </span>
    </div>

    <ul x-show="open && results.length" x-cloak @click.outside="open = false"
        class="absolute z-40 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
        style="max-height:15rem;overflow-y:auto;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;">
        <template x-for="(r, i) in results" :key="i">
            <li @click="pick(r)" @mouseenter="active = i"
                :class="active === i ? 'bg-brand-50 dark:bg-brand-600/10' : ''"
                class="cursor-pointer px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                <span x-text="r.label"></span>
            </li>
        </template>
    </ul>

    <p x-show="open && !results.length && !loading" x-cloak
       class="absolute z-30 mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-400 shadow-lg dark:border-gray-700 dark:bg-gray-800">
        Aucune adresse trouvée.
    </p>

    <p class="mt-1 text-xs text-gray-400">
        Sélectionnez une suggestion pour remplir automatiquement les champs ci-dessous.
    </p>
</div>
