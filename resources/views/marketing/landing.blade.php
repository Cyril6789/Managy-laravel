<!DOCTYPE html>
<html lang="fr" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Managy · Le logiciel de gestion pour ateliers & dépanneurs informatiques</title>
    <meta name="description" content="Managy est la plateforme tout-en-un pour gérer vos interventions, clients, devis et facturation. Créez votre espace en quelques secondes.">
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
<body class="min-h-full bg-white text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">

    {{-- Header --}}
    <header class="sticky top-0 z-30 border-b border-gray-100 bg-white/80 backdrop-blur dark:border-gray-800 dark:bg-gray-950/80">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="#" class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-lg font-bold text-white">M</span>
                <span class="text-lg font-semibold">Managy</span>
            </a>
            <nav class="hidden items-center gap-8 text-sm font-medium text-gray-600 md:flex dark:text-gray-300">
                <a href="#fonctionnalites" class="hover:text-brand-600">Fonctionnalités</a>
                <a href="#comment" class="hover:text-brand-600">Comment ça marche</a>
                <a href="#tarif" class="hover:text-brand-600">Tarif</a>
            </nav>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-brand-600 dark:text-gray-300">Connexion</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Créer mon espace</a>
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-x-0 -top-40 h-96 bg-gradient-to-b from-brand-100/60 to-transparent blur-3xl dark:from-brand-900/20"></div>
        <div class="mx-auto max-w-6xl px-6 py-20 text-center sm:py-28">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:border-brand-800 dark:bg-brand-900/30 dark:text-brand-300">
                ✨ Nouveau · Plateforme multi-entreprises
            </span>
            <h1 class="mx-auto mt-6 max-w-3xl text-4xl font-bold tracking-tight sm:text-6xl">
                Gérez votre atelier informatique,<br class="hidden sm:block"> sans la paperasse.
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600 dark:text-gray-300">
                Interventions, clients, devis, pièces, sous-traitance, facturation et suivi client&nbsp;:
                tout votre métier dans un seul outil. Créez l'espace de votre entreprise en moins d'une minute.
            </p>
            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('register') }}" class="w-full rounded-lg bg-brand-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-brand-700 sm:w-auto">
                    Démarrer gratuitement
                </a>
                <a href="{{ route('login') }}" class="w-full rounded-lg px-6 py-3 text-base font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 sm:w-auto dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-900">
                    J'ai déjà un compte
                </a>
            </div>
            <p class="mt-4 text-sm text-gray-400">Sans carte bancaire · Espace prêt à l'emploi avec données de démarrage</p>
        </div>
    </section>

    {{-- Features --}}
    <section id="fonctionnalites" class="mx-auto max-w-6xl px-6 py-16">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-bold tracking-tight">Tout ce qu'il faut pour faire tourner l'atelier</h2>
            <p class="mt-4 text-gray-600 dark:text-gray-300">Conçu avec et pour les techniciens.</p>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @php
                $features = [
                    ['🧾', 'Suivi des interventions', 'Du dépôt à la restitution : statuts personnalisables, photos, rapports et historique complet.'],
                    ['👥', 'Fiches clients', 'Particuliers et entreprises, contacts, matériel, pack maintenance et messagerie intégrée.'],
                    ['💶', 'Devis & facturation', 'Prestations, pièces, remises, déplacements et calcul automatique des montants.'],
                    ['📦', 'Commandes & sous-traitance', 'Suivez les pièces commandées et les retours de sous-traitants en un coup d\'œil.'],
                    ['📅', 'Planning & disponibilités', 'Calendrier, rendez-vous et gestion des absences de vos techniciens.'],
                    ['📊', 'Statistiques & satisfaction', 'Pilotez votre activité et collectez les avis clients automatiquement.'],
                ];
            @endphp
            @foreach ($features as [$icon, $title, $text])
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-xl dark:bg-brand-900/30">{{ $icon }}</div>
                    <h3 class="mt-4 text-lg font-semibold">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- How it works --}}
    <section id="comment" class="bg-gray-50 py-16 dark:bg-gray-900/40">
        <div class="mx-auto max-w-6xl px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight">Votre espace en 3 étapes</h2>
            </div>
            <div class="mt-12 grid gap-8 sm:grid-cols-3">
                @php
                    $steps = [
                        ['1', 'Inscrivez votre entreprise', 'Nom, SIRET, logo… votre identité est enregistrée comme une nouvelle société.'],
                        ['2', 'Votre espace est créé', 'Statuts, systèmes d\'exploitation, antivirus et prestations sont déjà pré-remplis.'],
                        ['3', 'Vous travaillez', 'Connectez-vous par e-mail et commencez à enregistrer vos interventions.'],
                    ];
                @endphp
                @foreach ($steps as [$n, $title, $text])
                    <div class="text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-600 text-lg font-bold text-white">{{ $n }}</div>
                        <h3 class="mt-4 text-lg font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section id="tarif" class="mx-auto max-w-6xl px-6 py-20">
        <div class="overflow-hidden rounded-3xl bg-brand-600 px-8 py-14 text-center text-white shadow-xl sm:px-16">
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Prêt à digitaliser votre atelier&nbsp;?</h2>
            <p class="mx-auto mt-4 max-w-xl text-brand-100">Créez l'espace de votre entreprise dès maintenant. C'est gratuit et instantané.</p>
            <a href="{{ route('register') }}" class="mt-8 inline-block rounded-lg bg-white px-6 py-3 text-base font-semibold text-brand-700 shadow-sm transition hover:bg-brand-50">
                Créer mon espace Managy
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-gray-100 dark:border-gray-800">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 py-8 text-sm text-gray-500 sm:flex-row">
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 text-sm font-bold text-white">M</span>
                <span>© {{ date('Y') }} Managy</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="{{ route('login') }}" class="hover:text-brand-600">Connexion</a>
                <a href="{{ route('register') }}" class="hover:text-brand-600">Inscription</a>
            </div>
        </div>
    </footer>
</body>
</html>
