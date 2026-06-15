@props(['color' => null])

@php
    // If a hex color is given, render a soft dot+label badge; else a neutral pill.
    $hex = $color && str_starts_with($color, '#');
@endphp

@if ($hex)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium']) }}
          style="background-color: {{ $color }}1a; color: {{ $color }};">
        <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $color }};"></span>
        {{ $slot }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300']) }}>
        {{ $slot }}
    </span>
@endif
