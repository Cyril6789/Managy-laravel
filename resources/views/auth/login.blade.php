<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · {{ $appSettings['company_name'] ?? config('app.name') }}</title>
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
<body class="flex min-h-full items-center justify-center bg-gray-100 px-4 py-12 dark:bg-gray-950">
    <div class="w-full max-w-sm">
        <div class="mb-8 flex flex-col items-center gap-3">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-2xl font-bold text-white">M</div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $appSettings['company_name'] ?? 'Managy' }}</h1>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-1 text-lg font-semibold text-gray-900 dark:text-gray-100">Connexion</h2>
            <p class="mb-5 text-sm text-gray-500">Accédez à votre espace de gestion.</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/30 dark:text-red-300">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <x-field label="Adresse e-mail" name="email" required>
                    <x-input name="email" type="email" value="{{ old('email') }}" autofocus autocomplete="email" />
                </x-field>

                <x-field label="Mot de passe" name="password" required>
                    <x-input name="password" type="password" autocomplete="current-password" />
                </x-field>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                        Se souvenir de moi
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm text-brand-600 hover:underline">Mot de passe oublié&nbsp;?</a>
                </div>

                <x-button type="submit" class="w-full">Se connecter</x-button>
            </form>

            <p class="mt-5 text-center text-sm text-gray-500">
                Pas encore de compte ?
                <a href="{{ route('register') }}" class="font-medium text-brand-600 hover:underline">Créer mon espace</a>
            </p>
        </div>

        <p class="mt-6 text-center text-xs text-gray-400">© {{ date('Y') }} {{ $appSettings['company_name'] ?? 'Managy' }}</p>
    </div>
    @livewireScripts
</body>
</html>
