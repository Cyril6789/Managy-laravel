<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérifiez votre e-mail · Managy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-gray-100 px-4 py-12 dark:bg-gray-950">
    <div class="w-full max-w-md text-center">
        <div class="mb-6 flex flex-col items-center gap-3">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-2xl font-bold text-white">M</div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Vérifiez votre adresse e-mail</h1>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            @if (session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700 dark:border-green-900 dark:bg-green-900/30 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            <p class="text-sm text-gray-600 dark:text-gray-300">
                Un lien de confirmation a été envoyé à votre adresse e-mail. Cliquez dessus pour activer votre espace.
                Vous ne l'avez pas reçu ?
            </p>

            <form action="{{ route('verification.send') }}" method="POST" class="mt-5">
                @csrf
                <x-button type="submit" class="w-full">Renvoyer le lien</x-button>
            </form>

            <form action="{{ route('logout') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-brand-600">Se déconnecter</button>
            </form>
        </div>
    </div>
    @livewireScripts
</body>
</html>
