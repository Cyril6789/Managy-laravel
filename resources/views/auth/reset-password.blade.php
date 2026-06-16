<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Réinitialiser le mot de passe · {{ $appSettings['company_name'] ?? config('app.name') }}</title>
    <script>(function(){const t=localStorage.getItem('theme');if(t==='dark'||(!t&&matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark');})();</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-gray-100 px-4 py-12 dark:bg-gray-950">
    <div class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="mb-5 text-lg font-semibold">Nouveau mot de passe</h2>

        @include('partials.flash')

        <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-field label="E-mail" name="email" required>
                <x-input name="email" type="email" value="{{ old('email', $email) }}" />
            </x-field>
            <x-field label="Nouveau mot de passe" name="password" required>
                <x-input name="password" type="password" autocomplete="new-password" />
            </x-field>
            <x-field label="Confirmer le mot de passe" name="password_confirmation" required>
                <x-input name="password_confirmation" type="password" autocomplete="new-password" />
            </x-field>
            <x-button type="submit" class="w-full">Réinitialiser</x-button>
        </form>
    </div>
    @livewireScripts
</body>
</html>
