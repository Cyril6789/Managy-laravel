@props([
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => '— Sélectionner —',
    'searchPlaceholder' => 'Rechercher…',
    'allowEmpty' => true,
    'disabled' => false,
])

@php
    $list = [];
    foreach ($options as $key => $value) {
        $list[] = is_array($value)
            ? ['value' => (string) $value['value'], 'label' => (string) $value['label']]
            : ['value' => (string) $key, 'label' => (string) $value];
    }
    $selected = $selected === null ? '' : (string) $selected;
@endphp

<div
    x-data="{
        open: false,
        query: '',
        value: @js($selected),
        options: @js($list),
        get selectedLabel() { return this.options.find(option => option.value === this.value)?.label || ''; },
        get filtered() {
            const term = this.query.trim().toLocaleLowerCase('fr');
            return term === '' ? this.options : this.options.filter(option => option.label.toLocaleLowerCase('fr').includes(term));
        },
        show() {
            this.open = true;
            this.query = '';
            this.$nextTick(() => this.$refs.search.focus());
        },
        choose(option) {
            this.value = option.value;
            this.open = false;
            this.query = '';
            this.$nextTick(() => this.$refs.input.dispatchEvent(new Event('change', { bubbles: true })));
            this.$nextTick(() => this.$refs.trigger.focus());
        },
        clear() {
            this.value = '';
            this.open = false;
            this.query = '';
            this.$nextTick(() => this.$refs.input.dispatchEvent(new Event('change', { bubbles: true })));
            this.$nextTick(() => this.$refs.trigger.focus());
        },
    }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
    class="relative"
>
    <input type="hidden" name="{{ $name }}" x-ref="input" x-model="value" {{ $attributes->except(['class', 'disabled']) }}>

    <button
        type="button"
        x-ref="trigger"
        x-on:click="show()"
        x-bind:aria-expanded="open"
        aria-haspopup="listbox"
        @disabled($disabled)
        {{ $attributes->except('disabled')->class(['flex w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100']) }}
    >
        <span x-text="selectedLabel || @js($placeholder)" x-bind:class="selectedLabel ? '' : 'text-gray-400'" class="truncate"></span>
        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
    </button>

    <div x-show="open" x-cloak x-transition.origin.top class="absolute z-40 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 p-2 dark:border-gray-700">
            <input
                type="search"
                x-ref="search"
                x-model="query"
                x-on:keydown.arrow-down.prevent="$refs.options.querySelector('[role=option]')?.focus()"
                placeholder="{{ $searchPlaceholder }}"
                class="w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-900"
            >
        </div>
        <ul x-ref="options" role="listbox" class="max-h-56 overflow-y-auto py-1 text-sm [touch-action:pan-y] [-webkit-overflow-scrolling:touch]">
            @if ($allowEmpty)
                <li><button type="button" role="option" x-on:click="clear()" class="block w-full px-3 py-2 text-left text-gray-400 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:hover:bg-gray-700 dark:focus:bg-gray-700">{{ $placeholder }}</button></li>
            @endif
            <template x-for="option in filtered" :key="option.value">
                <li>
                    <button type="button" role="option" x-on:click="choose(option)" x-on:keydown.arrow-down.prevent="$el.closest('li').nextElementSibling?.querySelector('button')?.focus()" x-on:keydown.arrow-up.prevent="$el.closest('li').previousElementSibling?.querySelector('button')?.focus()" class="block w-full px-3 py-2 text-left hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:hover:bg-gray-700 dark:focus:bg-gray-700">
                        <span x-text="option.label"></span>
                    </button>
                </li>
            </template>
            <li x-show="filtered.length === 0" class="px-3 py-2 text-gray-400">Aucun résultat.</li>
        </ul>
    </div>
</div>
