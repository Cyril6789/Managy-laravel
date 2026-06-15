@extends('layouts.app')
@section('title', 'Mon profil')

@section('content')
    <x-page-header title="Mon profil" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Informations">
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <x-field label="Prénom" name="prenom"><x-input name="prenom" value="{{ old('prenom', $user->prenom) }}" /></x-field>
                <x-field label="Nom" name="nom" required><x-input name="nom" value="{{ old('nom', $user->nom) }}" /></x-field>
                <x-field label="E-mail" name="email" required><x-input name="email" type="email" value="{{ old('email', $user->email) }}" /></x-field>
                <x-field label="Téléphone" name="telephone"><x-input name="telephone" value="{{ old('telephone', $user->telephone) }}" /></x-field>
                <div class="flex justify-end"><x-button type="submit">Enregistrer</x-button></div>
            </form>
        </x-card>

        <x-card title="Mot de passe">
            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <x-field label="Mot de passe actuel" name="current_password" required><x-input name="current_password" type="password" autocomplete="current-password" /></x-field>
                <x-field label="Nouveau mot de passe" name="password" required><x-input name="password" type="password" autocomplete="new-password" /></x-field>
                <x-field label="Confirmer" name="password_confirmation" required><x-input name="password_confirmation" type="password" autocomplete="new-password" /></x-field>
                <div class="flex justify-end"><x-button type="submit">Modifier le mot de passe</x-button></div>
            </form>
        </x-card>
    </div>
@endsection
