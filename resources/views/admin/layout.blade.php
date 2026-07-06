<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Supervision') · Managy Admin</title>

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

    @include('admin.partials.sidebar')

    <div class="lg:pl-64">
        @include('admin.partials.header')

        <main class="py-6">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                         class="mb-6 flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-900/30 dark:text-green-300">
                        <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>{{ session('success') }}</span>
                        <button @click="show = false" class="ml-auto text-green-600">&times;</button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>
