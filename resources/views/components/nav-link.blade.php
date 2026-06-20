@props(['route', 'icon' => 'dot', 'count' => null])

@php
    $href = \Illuminate\Support\Facades\Route::has($route) ? route($route) : '#';
    // Active state: treat a `*.index` link as the resource root (so detail pages
    // such as interventions.show keep it lit), otherwise match the exact route —
    // this avoids two siblings sharing a prefix (reception.commandes vs
    // reception.sous_traitances) lighting up together.
    $prefix = \Illuminate\Support\Str::endsWith($route, '.index')
        ? \Illuminate\Support\Str::beforeLast($route, '.index')
        : $route;
    $active = request()->routeIs($prefix) || request()->routeIs($prefix.'.*');
@endphp

<a href="{{ $href }}"
   @click="sidebarOpen = false"
   class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
          {{ $active
              ? 'bg-brand-50 text-brand-700 dark:bg-brand-600/15 dark:text-brand-300'
              : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">
    <x-icon :name="$icon" class="h-5 w-5 {{ $active ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}" />
    <span class="flex-1">{{ $slot }}</span>
    @if (! is_null($count) && $count > 0)
        <span class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-semibold
                     {{ $active ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
</a>

