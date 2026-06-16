<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Suivi') · {{ $appSettings['company_name'] ?? config('app.name') }}</title>
    <script>(function(){const t=localStorage.getItem('theme');if(t==='dark'||(!t&&matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark');})();</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full bg-gray-100 text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
    <header class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-4">
            <div class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 font-bold text-white">M</div>
                <span class="font-semibold">{{ $appSettings['company_name'] ?? 'Managy' }}</span>
            </div>
            <button type="button" @click="$store.theme.toggle()" x-data
                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                <span x-show="!$store.theme.dark"><x-icon name="moon" /></span>
                <span x-show="$store.theme.dark" x-cloak><x-icon name="sun" /></span>
            </button>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-8">
        @yield('content')
    </main>

    <footer class="py-8 text-center text-xs text-gray-400">
        @if (!empty($appSettings['company_phone'])) {{ $appSettings['company_phone'] }} · @endif
        © {{ date('Y') }} {{ $appSettings['company_name'] ?? 'Managy' }}
    </footer>
    @livewireScripts
</body>
</html>
