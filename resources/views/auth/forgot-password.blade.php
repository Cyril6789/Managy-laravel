<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mot de passe oublié · {{ $appSettings['company_name'] ?? config('app.name') }}</title>
    <script>(function(){const t=localStorage.getItem('theme');if(t==='dark'||(!t&&matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark');})();</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-gray-100 px-4 py-12 dark:bg-gray-950">
    <div class="w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="mb-1 text-lg font-semibold">Mot de passe oublié</h2>
        <p class="mb-5 text-sm text-gray-500">Saisissez votre e-mail, nous vous enverrons un lien de réinitialisation.</p>

        @include('partials.flash')

        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            <x-field label="E-mail" name="email" required>
                <x-input name="email" type="email" value="{{ old('email') }}" autofocus />
            </x-field>
            <x-button type="submit" class="w-full">Envoyer le lien</x-button>
        </form>

        <a href="{{ route('login') }}" class="mt-4 block text-center text-sm text-brand-600 hover:underline">Retour à la connexion</a>
    </div>
</body>
</html>
