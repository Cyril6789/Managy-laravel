@props([
    'name',
    'options' => [],            // assoc [value => label] OR list of ['value','label']
    'selected' => null,
    'placeholder' => '— Sélectionner —',
    'searchPlaceholder' => 'Rechercher…',
    'allowEmpty' => true,
])

@php
    $list = [];
    foreach ($options as $key => $val) {
        if (is_array($val)) {
            $list[] = ['value' => (string) $val['value'], 'label' => (string) $val['label']];
        } else {
            $list[] = ['value' => (string) $key, 'label' => (string) $val];
        }
    }
    $selected = $selected === null ? '' : (string) $selected;
@endphp

<div {{ $attributes->only('class') }}
     x-data="searchableSelect({ options: @js($list), selected: @js($selected), allowEmpty: {{ $allowEmpty ? 'true' : 'false' }} })"
     @keydown.escape="open = false" class="relative">
    <input type="hidden" name="{{ $name }}" x-ref="input" :value="value">

    <button type="button" @click="toggle()"
            class="flex w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
        <span x-text="label() || @js($placeholder)" :class="value ? '' : 'text-gray-400'" class="truncate"></span>
        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false" x-transition.opacity
         class="absolute z-40 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 p-2 dark:border-gray-700">
            <input x-ref="search" x-model="query" type="text" placeholder="{{ $searchPlaceholder }}"
                   class="w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-900">
        </div>
        <ul class="rounded-b-lg py-1 text-sm" style="max-height:15rem;overflow-y:auto;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;">
            <template x-if="allowEmpty">
                <li @click="pick('')" class="cursor-pointer px-3 py-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">{{ $placeholder }}</li>
            </template>
            <template x-for="opt in filtered()" :key="opt.value">
                <li @click="pick(opt.value)" x-text="opt.label"
                    :class="opt.value === value ? 'bg-brand-50 text-brand-700 dark:bg-brand-600/20 dark:text-brand-300' : ''"
                    class="cursor-pointer truncate px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"></li>
            </template>
            <li x-show="filtered().length === 0" class="px-3 py-2 text-gray-400">Aucun résultat</li>
        </ul>
    </div>
</div>
