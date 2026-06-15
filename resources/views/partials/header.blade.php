@php
    $user = auth()->user();
    $unread = $user ? $user->appNotifications()->whereNull('read_at')->count() : 0;
@endphp

<header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-gray-200 bg-white/90 px-4 backdrop-blur dark:border-gray-800 dark:bg-gray-900/90 sm:px-6 lg:px-8">
    {{-- Mobile menu --}}
    <button type="button" @click="sidebarOpen = true"
            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 lg:hidden">
        <x-icon name="menu" />
    </button>

    {{-- Search --}}
    <form action="{{ route('search') }}" method="GET" class="relative hidden flex-1 max-w-md sm:block">
        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un client, une intervention…"
               class="w-full rounded-lg border-gray-300 bg-gray-50 pl-9 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800" />
    </form>

    <div class="ml-auto flex items-center gap-1.5">
        {{-- Theme toggle --}}
        <button type="button" @click="$store.theme.toggle()"
                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" title="Thème clair / sombre">
            <span x-show="!$store.theme.dark"><x-icon name="moon" /></span>
            <span x-show="$store.theme.dark" x-cloak><x-icon name="sun" /></span>
        </button>

        {{-- Notifications --}}
        <div class="relative" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                    class="relative rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                <x-icon name="bell" />
                @if ($unread > 0)
                    <span class="absolute right-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $unread > 9 ? '9+' : $unread }}</span>
                @endif
            </button>
            <div x-show="open" x-cloak @click.outside="open = false" x-transition
                 class="absolute right-0 mt-2 w-80 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2.5 dark:border-gray-800">
                    <span class="text-sm font-semibold">Notifications</span>
                    @if ($unread > 0)
                        <form action="{{ route('notifications.read_all') }}" method="POST">@csrf
                            <button class="text-xs text-brand-600 hover:underline">Tout marquer lu</button>
                        </form>
                    @endif
                </div>
                <div class="max-h-80 divide-y divide-gray-100 overflow-y-auto dark:divide-gray-800">
                    @forelse ($user?->appNotifications()->limit(8)->get() ?? [] as $n)
                        <a href="{{ $n->url ?? '#' }}" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 {{ $n->read_at ? '' : 'bg-brand-50/50 dark:bg-brand-600/10' }}">
                            <p class="text-sm font-medium">{{ $n->titre }}</p>
                            @if ($n->message)<p class="text-xs text-gray-500">{{ $n->message }}</p>@endif
                            <p class="mt-0.5 text-[11px] text-gray-400">{{ $n->created_at?->diffForHumans() }}</p>
                        </a>
                    @empty
                        <p class="px-4 py-8 text-center text-sm text-gray-400">Aucune notification</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- User menu --}}
        <div class="relative" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-lg p-1 pl-1.5 hover:bg-gray-100 dark:hover:bg-gray-800">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-xs font-semibold text-white">{{ $user?->initials() }}</span>
                <span class="hidden text-sm font-medium sm:block">{{ $user?->fullName() }}</span>
            </button>
            <div x-show="open" x-cloak @click.outside="open = false" x-transition
                 class="absolute right-0 mt-2 w-52 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-4 py-2 dark:border-gray-800">
                    <p class="truncate text-sm font-semibold">{{ $user?->fullName() }}</p>
                    <p class="truncate text-xs text-gray-500">{{ $user?->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">Mon profil</a>
                <form action="{{ route('logout') }}" method="POST">@csrf
                    <button class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <x-icon name="logout" class="h-4 w-4" /> Se déconnecter
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
