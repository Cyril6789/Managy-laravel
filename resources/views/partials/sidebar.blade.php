@php
    // Nav definition: [route, label, icon key, gate (null = always)]
    $nav = [
        ['dashboard', 'Tableau de bord', 'home', null],
        ['interventions.index', 'Interventions', 'wrench', \App\Support\Permissions::INTERVENTIONS_VIEW],
        ['calendar.index', 'Calendrier', 'calendar', \App\Support\Permissions::CALENDAR_VIEW],
        ['clients.index', 'Clients', 'users', \App\Support\Permissions::CLIENTS_VIEW],
        ['tasks.index', 'Tâches', 'check', \App\Support\Permissions::TASKS_VIEW],
        ['maintenance.index', 'Pack maintenance', 'shield', \App\Support\Permissions::MAINTENANCE_VIEW],
        ['stats.index', 'Statistiques', 'chart', \App\Support\Permissions::STATS_VIEW],
        ['satisfaction.index', 'Satisfaction', 'star', \App\Support\Permissions::SATISFACTION_VIEW],
    ];
    $admin = [
        ['staff.index', 'Techniciens', 'id', \App\Support\Permissions::STAFF_MANAGE],
        ['automatismes.index', 'Automatismes', 'bolt', \App\Support\Permissions::AUTOMATISMES_MANAGE],
        ['settings.index', 'Paramètres', 'cog', \App\Support\Permissions::SETTINGS_MANAGE],
        ['logs.index', 'Journaux', 'list', \App\Support\Permissions::LOGS_VIEW],
    ];
@endphp

<aside
    class="fixed inset-y-0 left-0 z-40 w-64 transform border-r border-gray-200 bg-white transition-transform duration-200 ease-in-out dark:border-gray-800 dark:bg-gray-900 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

    <div class="flex h-16 items-center gap-2 border-b border-gray-200 px-5 dark:border-gray-800">
        @if (!empty($appSettings['company_logo']))
            <img src="{{ \Illuminate\Support\Facades\Storage::url($appSettings['company_logo']) }}" alt="logo" class="h-9 max-w-[140px] object-contain">
        @else
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 font-bold text-white">M</div>
            <span class="truncate text-lg font-semibold">{{ $appSettings['company_name'] ?? 'Managy' }}</span>
        @endif
    </div>

    <nav class="flex h-[calc(100%-4rem)] flex-col gap-1 overflow-y-auto p-3">
        @foreach ($nav as [$route, $label, $icon, $gate])
            @if (! $gate || auth()->user()?->can($gate))
                <x-nav-link :route="$route" :icon="$icon">{{ $label }}</x-nav-link>
            @endif
        @endforeach

        @php $showAdmin = collect($admin)->contains(fn ($i) => auth()->user()?->can($i[3])); @endphp
        @if ($showAdmin)
            <p class="mt-4 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Administration</p>
            @foreach ($admin as [$route, $label, $icon, $gate])
                @can($gate)
                    <x-nav-link :route="$route" :icon="$icon">{{ $label }}</x-nav-link>
                @endcan
            @endforeach
        @endif
    </nav>
</aside>
