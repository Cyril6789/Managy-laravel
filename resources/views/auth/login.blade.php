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

            <div x-data="{ email: @js(old('email')) }">
                <form action="{{ route('login') }}" method="POST" class="space-y-4">
                    @csrf
                    <x-field label="Adresse e-mail" name="email" required>
                        <x-input name="email" type="email" x-model="email" autofocus autocomplete="email" />
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

                {{-- Single Sign-On: the société is resolved from the e-mail domain typed above. --}}
                <div class="my-5 flex items-center gap-3 text-xs uppercase tracking-wide text-gray-400">
                    <span class="h-px flex-1 bg-gray-200 dark:bg-gray-800"></span>ou<span class="h-px flex-1 bg-gray-200 dark:bg-gray-800"></span>
                </div>

                <div class="space-y-2">
                    <form action="{{ route('sso.redirect', 'microsoft') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" :value="email">
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg class="h-4 w-4" viewBox="0 0 23 23" aria-hidden="true"><path fill="#f25022" d="M1 1h10v10H1z"/><path fill="#7fba00" d="M12 1h10v10H12z"/><path fill="#00a4ef" d="M1 12h10v10H1z"/><path fill="#ffb900" d="M12 12h10v10H12z"/></svg>
                            Continuer avec Microsoft
                        </button>
                    </form>
                    <form action="{{ route('sso.redirect', 'google') }}" method="POST">
                        @csrf
                        <input type="hidden" name="email" :value="email">
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            <svg class="h-4 w-4" viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                            Continuer avec Google
                        </button>
                    </form>
                    <p class="pt-1 text-center text-xs text-gray-400">Saisissez d'abord votre e-mail professionnel ci-dessus.</p>
                </div>
            </div>

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
