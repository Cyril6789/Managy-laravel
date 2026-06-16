@props(['label' => null, 'name' => null, 'required' => false, 'hint' => null])

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}@if ($required)<span class="text-red-500"> *</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="mt-1 text-xs text-gray-400">{{ $hint }}</p>
    @endif

    @if ($name && $errors->has($name))
        <p class="mt-1 text-xs text-red-600">{{ $errors->first($name) }}</p>
    @endif
</div>
