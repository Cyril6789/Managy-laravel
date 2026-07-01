<!DOCTYPE html>
<html lang="fr" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Managy · Le logiciel de gestion pour ateliers & dépanneurs informatiques</title>
    <meta name="description" content="Managy est la plateforme tout-en-un pour gérer vos interventions, vos clients, votre planning et vos sous-traitances. Créez l'espace de votre entreprise en quelques secondes.">
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
<body class="min-h-full bg-white text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100" x-data="{ mobileNavOpen: false }">

    {{-- Header --}}
    <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/80 backdrop-blur dark:border-gray-800 dark:bg-gray-950/80">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-lg font-bold text-white">M</span>
                <span class="text-lg font-semibold">Managy</span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-gray-600 md:flex dark:text-gray-300">
                <a href="#fonctionnalites" class="transition hover:text-brand-600">Fonctionnalités</a>
                <a href="#comment" class="transition hover:text-brand-600">Comment ça marche</a>
                <a href="#faq" class="transition hover:text-brand-600">FAQ</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 transition hover:text-brand-600 dark:text-gray-300">Connexion</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Créer mon espace</a>
            </div>

            <button type="button" class="-mr-2 flex h-10 w-10 items-center justify-center rounded-lg text-gray-600 md:hidden dark:text-gray-300"
                    @click="mobileNavOpen = !mobileNavOpen" :aria-expanded="mobileNavOpen" aria-label="Ouvrir le menu">
                <x-icon name="menu" x-show="!mobileNavOpen" class="h-6 w-6" />
                <x-icon name="plus" x-show="mobileNavOpen" x-cloak class="h-6 w-6 rotate-45" />
            </button>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobileNavOpen" x-cloak x-transition @click.outside="mobileNavOpen = false"
             class="border-t border-gray-100 bg-white px-6 py-4 md:hidden dark:border-gray-800 dark:bg-gray-950">
            <nav class="flex flex-col gap-1 text-sm font-medium text-gray-600 dark:text-gray-300">
                <a href="#fonctionnalites" class="rounded-lg px-2 py-2.5 hover:bg-gray-50 hover:text-brand-600 dark:hover:bg-gray-900" @click="mobileNavOpen = false">Fonctionnalités</a>
                <a href="#comment" class="rounded-lg px-2 py-2.5 hover:bg-gray-50 hover:text-brand-600 dark:hover:bg-gray-900" @click="mobileNavOpen = false">Comment ça marche</a>
                <a href="#faq" class="rounded-lg px-2 py-2.5 hover:bg-gray-50 hover:text-brand-600 dark:hover:bg-gray-900" @click="mobileNavOpen = false">FAQ</a>
                <a href="{{ route('login') }}" class="rounded-lg px-2 py-2.5 hover:bg-gray-50 hover:text-brand-600 dark:hover:bg-gray-900">Connexion</a>
            </nav>
            <a href="{{ route('register') }}" class="mt-3 block rounded-lg bg-brand-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                Créer mon espace
            </a>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-x-0 -top-40 -z-10 h-[36rem] bg-gradient-to-b from-brand-100/70 via-brand-50/30 to-transparent blur-3xl dark:from-brand-900/25 dark:via-brand-900/5"></div>
        <div class="pointer-events-none absolute -right-32 top-24 -z-10 h-72 w-72 rounded-full bg-purple-200/40 blur-3xl dark:bg-purple-900/10"></div>

        <div class="mx-auto max-w-6xl px-6 pt-16 pb-8 text-center sm:pt-24">
            
            <h1 class="mx-auto mt-6 max-w-3xl text-4xl font-bold tracking-tight sm:text-6xl">
                Gérez votre atelier informatique,<br class="hidden sm:block"> sans la paperasse.
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600 dark:text-gray-300">
                Interventions, clients, planning, pièces et sous-traitance&nbsp;: tout le quotidien de votre atelier
                dans un seul outil. Créez l'espace de votre entreprise en moins d'une minute.
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

        {{-- Product mockup --}}
        <div class="relative mx-auto mt-12 max-w-4xl px-6 pb-4 sm:mt-16 sm:pb-8">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl shadow-brand-900/10 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-1.5 border-b border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/60">
                    <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-yellow-400"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-green-400"></span>
                    <span class="ml-4 truncate rounded-md bg-white px-3 py-1 text-xs text-gray-400 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-500 dark:ring-gray-700">
                        app.managy.fr/tableau-de-bord
                    </span>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-3 sm:p-6">
                    @php
                        $board = [
                            ['Nouveau', 'bg-gray-300 dark:bg-gray-600', [
                                ['Dupont · PC ne démarre plus', 'SM'],
                                ['Martin · Écran cassé', 'JD'],
                            ]],
                            ['En cours', 'bg-amber-400', [
                                ['Bernard · Suspicion virus', 'JD'],
                                ['Lefèvre · Upgrade SSD', 'AL'],
                            ]],
                            ['Terminé', 'bg-emerald-500', [
                                ['Petit · Réinstallation', 'SM'],
                                ['Garcia · Nettoyage', 'AL'],
                            ]],
                        ];
                    @endphp
                    @foreach ($board as [$label, $dot, $cards])
                        <div>
                            <div class="mb-3 flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                <span class="h-2 w-2 rounded-full {{ $dot }}"></span>
                                {{ $label }}
                            </div>
                            <div class="space-y-2.5">
                                @foreach ($cards as [$title, $initials])
                                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 text-left dark:border-gray-800 dark:bg-gray-800/60">
                                        <p class="text-xs font-medium text-gray-700 dark:text-gray-200">{{ $title }}</p>
                                        <div class="mt-2 flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 text-[10px] font-semibold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                                            {{ $initials }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="absolute -left-2 -top-4 hidden -rotate-3 items-center gap-2 rounded-xl border border-gray-100 bg-white px-3.5 py-2 text-xs font-medium shadow-lg sm:flex dark:border-gray-800 dark:bg-gray-900">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                    <x-icon name="check" class="h-3.5 w-3.5" />
                </span>
                Intervention clôturée
            </div>
            <div class="absolute -right-2 -bottom-4 hidden rotate-3 items-center gap-2 rounded-xl border border-gray-100 bg-white px-3.5 py-2 text-xs font-medium shadow-lg sm:flex dark:border-gray-800 dark:bg-gray-900">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">
                    <x-icon name="star" class="h-3.5 w-3.5" />
                </span>
                Satisfaction client 5/5
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="fonctionnalites" class="mx-auto max-w-6xl px-6 py-20 sm:py-24">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-400">Fonctionnalités</p>
            <h2 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Tout ce qu'il faut pour faire tourner l'atelier</h2>
            <p class="mt-4 text-gray-600 dark:text-gray-300">Conçu avec et pour les techniciens de terrain.</p>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @php
                $features = [
                    ['wrench', 'Suivi des interventions', 'Du dépôt à la restitution : statuts personnalisables, photos, rapports et historique complet.'],
                    ['users', 'Fiches clients', 'Particuliers et entreprises, contacts, matériel suivi et pack de maintenance.'],
                    ['truck', 'Commandes & sous-traitance', "Suivez les pièces commandées et les retours de sous-traitants en un coup d'œil."],
                    ['calendar', 'Planning & disponibilités', 'Calendrier, rendez-vous et gestion des absences de vos techniciens.'],
                    ['bolt', 'Automatisations SMS & e-mail', 'Notifications automatiques à chaque étape clé, avec vos propres modèles de messages.'],
                    ['chart', 'Statistiques & satisfaction', "Pilotez votre activité et collectez l'avis de vos clients automatiquement."],
                ];
            @endphp
            @foreach ($features as [$icon, $title, $text])
                <div class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white dark:bg-brand-900/30 dark:text-brand-300">
                        <x-icon :name="$icon" class="h-5 w-5" />
                    </div>
                    <h3 class="mt-4 text-lg font-semibold">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- How it works --}}
    <section id="comment" class="bg-gray-50 py-20 sm:py-24 dark:bg-gray-900/40">
        <div class="mx-auto max-w-6xl px-6">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-400">Mise en route</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Votre espace en 3 étapes</h2>
            </div>
            <div class="mt-12 grid gap-10 sm:grid-cols-3">
                @php
                    $steps = [
                        ['1', 'Inscrivez votre entreprise', 'Nom, SIRET, logo… votre identité est enregistrée comme une nouvelle société, isolée des autres.'],
                        ['2', 'Votre espace est créé', "Des réglages de bases appliqués pour commencer à travailler tout de suite. Vous pourrez tout modifier ensuite !"],
                        ['3', 'Vous travaillez', 'Connectez-vous, invitez votre équipe et commencez à enregistrer vos interventions.'],
                    ];
                @endphp
                @foreach ($steps as [$n, $title, $text])
                    <div class="relative text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-600 text-lg font-bold text-white">{{ $n }}</div>
                        <h3 class="mt-4 text-lg font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- FAQ --}}
    <section id="faq" class="bg-gray-50 py-20 sm:py-24 dark:bg-gray-900/40">
        <div class="mx-auto max-w-3xl px-6">
            <div class="text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-400">FAQ</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Des questions&nbsp;?</h2>
            </div>
            <div class="mt-10 space-y-3">
                @php
                    $faqs = [
                        [
                            'Managy gère-t-il la facturation ?',
                            "Non. Managy se concentre sur le suivi des interventions et la relation client : vous gardez votre outil de facturation actuel, Managy s'occupe du reste.",
                        ],
                        [
                            'Puis-je essayer sans engagement ?',
                            'Oui, créez votre espace en quelques secondes, sans carte bancaire, et arrêtez quand vous voulez.',
                        ],
                        [
                            'Puis-je inviter mon équipe de techniciens ?',
                            'Oui, ajoutez vos collaborateurs, gérez leurs disponibilités et affectez-leur des interventions directement depuis Managy.',
                        ],
                    ];
                @endphp
                @foreach ($faqs as [$question, $answer])
                    <details class="group rounded-xl border border-gray-100 bg-white p-5 open:shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-medium text-gray-900 dark:text-gray-100">
                            {{ $question }}
                            <x-icon name="plus" class="h-4 w-4 shrink-0 text-gray-400 transition group-open:rotate-45" />
                        </summary>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ $answer }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="mx-auto max-w-6xl px-6 py-20">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-brand-700 px-8 py-14 text-center text-white shadow-xl sm:px-16">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
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
                <a href="#fonctionnalites" class="hover:text-brand-600">Fonctionnalités</a>
                <a href="{{ route('login') }}" class="hover:text-brand-600">Connexion</a>
                <a href="{{ route('register') }}" class="hover:text-brand-600">Inscription</a>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
