@props(['route', 'icon' => 'dot'])

@php
    $href = \Illuminate\Support\Facades\Route::has($route) ? route($route) : '#';
    // Active when current route matches the link's base segment (e.g. interventions.*).
    $base = \Illuminate\Support\Str::before($route, '.');
    $active = request()->routeIs($base.'*') || request()->routeIs($route);
@endphp

<a href="{{ $href }}"
   @click="sidebarOpen = false"
   class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
          {{ $active
              ? 'bg-brand-50 text-brand-700 dark:bg-brand-600/15 dark:text-brand-300'
              : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">
    <x-icon :name="$icon" class="h-5 w-5 {{ $active ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}" />
    <span>{{ $slot }}</span>
</a>
