@php
    // Nav definition: [route, label, icon, active?]
    $nav = [
        ['admin.dashboard', 'Sociétés', 'chart', request()->routeIs('admin.dashboard') || request()->routeIs('admin.society')],
        ['admin.support.index', 'Assistance', 'lifebuoy', request()->routeIs('admin.support.*')],
    ];
@endphp

<aside
    class="fixed inset-y-0 left-0 z-40 w-64 transform border-r border-gray-200 bg-white transition-transform duration-200 ease-in-out dark:border-gray-800 dark:bg-gray-900 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

    <div class="flex h-16 items-center gap-2 border-b border-gray-200 px-5 dark:border-gray-800">
        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-900 font-bold text-white dark:bg-white dark:text-gray-900">M</span>
        <span class="truncate text-lg font-semibold">Managy</span>
        <span class="rounded-md bg-brand-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">Supervision</span>
    </div>

    <nav class="flex h-[calc(100%-4rem)] flex-col gap-1 overflow-y-auto p-3">
        @foreach ($nav as [$route, $label, $icon, $active])
            <a href="{{ route($route) }}" @click="sidebarOpen = false"
               class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                      {{ $active
                          ? 'bg-brand-50 text-brand-700 dark:bg-brand-600/15 dark:text-brand-300'
                          : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                <x-icon :name="$icon" class="h-5 w-5 {{ $active ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}" />
                <span class="flex-1">{{ $label }}</span>
            </a>
        @endforeach

        <div class="mt-auto border-t border-gray-200 pt-2 dark:border-gray-800">
            <a href="{{ route('admin.account') }}" @click="sidebarOpen = false"
               class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                      {{ request()->routeIs('admin.account')
                          ? 'bg-brand-50 text-brand-700 dark:bg-brand-600/15 dark:text-brand-300'
                          : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">
                <x-icon name="id" class="h-5 w-5 {{ request()->routeIs('admin.account') ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}" />
                <span class="flex-1">Mon compte</span>
            </a>
        </div>
    </nav>
</aside>
