<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer mon espace · Managy</title>
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
    <div class="w-full max-w-lg">
        <div class="mb-8 flex flex-col items-center gap-3">
            <a href="{{ route('home') }}" class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-2xl font-bold text-white">M</a>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Créer votre espace Managy</h1>
            <p class="text-sm text-gray-500">Votre entreprise et votre compte en une étape.</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/30 dark:text-red-300">
                    <ul class="list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Votre entreprise</h2>
                    <div class="mt-3 space-y-4">
                        <x-field label="Nom de l'entreprise" name="company_name" required>
                            <x-input name="company_name" value="{{ old('company_name') }}" autofocus required />
                        </x-field>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-field label="SIRET" name="company_siret">
                                <x-input name="company_siret" value="{{ old('company_siret') }}" />
                            </x-field>
                            <x-field label="Téléphone" name="company_phone">
                                <x-input name="company_phone" value="{{ old('company_phone') }}" />
                            </x-field>
                        </div>
                        <x-field label="Adresse" name="company_address">
                            <x-input name="company_address" value="{{ old('company_address') }}" />
                        </x-field>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <x-field label="Code postal" name="company_postal_code">
                                <x-input name="company_postal_code" value="{{ old('company_postal_code') }}" />
                            </x-field>
                            <div class="sm:col-span-2">
                                <x-field label="Ville" name="company_city">
                                    <x-input name="company_city" value="{{ old('company_city') }}" />
                                </x-field>
                            </div>
                        </div>
                        <x-field label="Logo (facultatif)" name="logo">
                            <input type="file" name="logo" accept="image/*"
                                   class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:text-gray-300" />
                        </x-field>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5 dark:border-gray-800">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Votre compte (gérant)</h2>
                    <div class="mt-3 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-field label="Prénom" name="prenom">
                                <x-input name="prenom" value="{{ old('prenom') }}" autocomplete="given-name" />
                            </x-field>
                            <x-field label="Nom" name="nom" required>
                                <x-input name="nom" value="{{ old('nom') }}" autocomplete="family-name" required />
                            </x-field>
                        </div>
                        <x-field label="Adresse e-mail" name="email" required>
                            <x-input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required />
                        </x-field>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-field label="Mot de passe" name="password" required>
                                <x-input name="password" type="password" autocomplete="new-password" required />
                            </x-field>
                            <x-field label="Confirmation" name="password_confirmation" required>
                                <x-input name="password_confirmation" type="password" autocomplete="new-password" required />
                            </x-field>
                        </div>
                    </div>
                </div>

                <x-button type="submit" class="w-full">Créer mon espace</x-button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-gray-500">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:underline">Se connecter</a>
        </p>
    </div>
    @livewireScripts
</body>
</html>
