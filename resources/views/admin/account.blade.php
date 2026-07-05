@extends('admin.layout')

@section('title', 'Mon compte')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight">Mon compte</h1>
        <p class="mt-1 text-sm text-gray-500">Gérez vos informations et votre mot de passe de super-administrateur.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/30 dark:text-red-300">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Informations --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-4 text-base font-semibold">Informations</h2>
            <form action="{{ route('admin.account.update') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label for="prenom" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Prénom</label>
                    <input id="prenom" name="prenom" type="text" value="{{ old('prenom', $user->prenom) }}"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                </div>
                <div>
                    <label for="nom" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nom <span class="text-red-500">*</span></label>
                    <input id="nom" name="nom" type="text" required value="{{ old('nom', $user->nom) }}"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">E-mail <span class="text-red-500">*</span></label>
                    <input id="email" name="email" type="email" required value="{{ old('email', $user->email) }}"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">Enregistrer</button>
                </div>
            </form>
        </div>

        {{-- Mot de passe --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-4 text-base font-semibold">Mot de passe</h2>
            <form action="{{ route('admin.account.password') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label for="current_password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Mot de passe actuel <span class="text-red-500">*</span></label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nouveau mot de passe <span class="text-red-500">*</span></label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmer <span class="text-red-500">*</span></label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">Modifier le mot de passe</button>
                </div>
            </form>
        </div>
    </div>
@endsection
