@php $user = auth()->user(); @endphp

<header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-gray-200 bg-white px-4 dark:border-gray-800 dark:bg-gray-900 sm:px-6 lg:px-8">
    {{-- Mobile menu --}}
    <button type="button" @click="sidebarOpen = true"
            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 lg:hidden">
        <x-icon name="menu" />
    </button>

    <span class="text-sm font-medium text-gray-500 lg:hidden">Supervision</span>

    <div class="ml-auto flex items-center gap-1.5">
        {{-- Theme toggle --}}
        <button type="button" @click="$store.theme.toggle()"
                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" title="Thème clair / sombre">
            <span x-show="!$store.theme.dark"><x-icon name="moon" /></span>
            <span x-show="$store.theme.dark" x-cloak><x-icon name="sun" /></span>
        </button>

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
                <a href="{{ route('admin.account') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">Mon compte</a>
                <form action="{{ route('logout') }}" method="POST">@csrf
                    <button class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <x-icon name="logout" class="h-4 w-4" /> Se déconnecter
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
