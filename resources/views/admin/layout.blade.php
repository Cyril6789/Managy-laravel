<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Supervision') · Managy Admin</title>
    <script>
        (function () {
            const t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-gray-100 text-gray-900 dark:bg-gray-950 dark:text-gray-100">
    <header class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-900 text-sm font-bold text-white dark:bg-white dark:text-gray-900">M</span>
                <span class="font-semibold">Managy</span>
                <span class="rounded-md bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">SUPERVISION</span>
            </a>
            <div class="flex items-center gap-4 text-sm">
                <span class="text-gray-500">{{ auth()->user()->fullName() }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="font-medium text-gray-600 hover:text-brand-600 dark:text-gray-300">Déconnexion</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-8">
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-2.5 text-sm text-green-700 dark:border-green-900 dark:bg-green-900/30 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>
    @livewireScripts
</body>
</html>
