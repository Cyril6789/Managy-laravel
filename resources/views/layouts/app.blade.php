<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord') · {{ $appSettings['company_name'] ?? config('app.name') }}</title>

    {{-- Apply persisted theme before paint to avoid flash --}}
    <script>
        (function () {
            const t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-gray-100 text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
<div x-data="{ sidebarOpen: false }" class="min-h-full">

    {{-- Mobile sidebar backdrop --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden" x-transition.opacity></div>

    @include('partials.sidebar')

    <div class="lg:pl-64">
        @include('partials.header')

        <main class="py-6">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                @include('partials.flash')
                @yield('content')
            </div>
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>
