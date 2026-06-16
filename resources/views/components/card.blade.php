@props(['title' => null, 'padding' => true])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900']) }}>
    @if ($title || isset($actions))
        <div class="flex items-center justify-between gap-2 border-b border-gray-100 px-5 py-3 dark:border-gray-800">
            <h3 class="font-semibold">{{ $title }}</h3>
            @isset($actions){{ $actions }}@endisset
        </div>
    @endif
    <div class="{{ $padding ? 'p-5' : '' }}">
        {{ $slot }}
    </div>
</div>
