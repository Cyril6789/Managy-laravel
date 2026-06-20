@extends('layouts.app')
@section('title', 'Paramètres')

@section('content')
    <x-page-header title="Paramètres" />

    <div x-data="{ tab: 'entreprise' }">
        <div class="mb-6 flex flex-wrap gap-1 border-b border-gray-200 dark:border-gray-800">
            @foreach (['entreprise' => 'Entreprise', 'sms' => 'SMS', 'smtp' => 'E-mail (SMTP)', 'listes' => 'Listes métier', 'statuts' => 'Statuts', 'facturation' => 'Facturation', 'modeles' => 'Modèles'] as $key => $label)
                <button @click="tab='{{ $key }}'" :class="tab==='{{ $key }}' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-4 py-2 text-sm font-medium">{{ $label }}</button>
            @endforeach
        </div>

        {{-- Entreprise --}}
        <div x-show="tab==='entreprise'">
            <livewire:settings.general-settings section="entreprise" />
        </div>

        {{-- SMS --}}
        <div x-show="tab==='sms'" x-cloak>
            <livewire:settings.general-settings section="sms" />
        </div>

        {{-- SMTP / E-mail --}}
        <div x-show="tab==='smtp'" x-cloak>
            <livewire:settings.general-settings section="smtp" />
        </div>

        {{-- Listes métier (tout en Livewire, édition sans rechargement) --}}
        <div x-show="tab==='listes'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <livewire:settings.reference-list type="materiels" title="Types de matériel" />
            <livewire:settings.reference-list type="systemes" title="Systèmes d'exploitation" />
            <livewire:settings.reference-list type="antivirus" title="Antivirus" />
            <livewire:settings.reference-list type="prestations" title="Prestations" />
        </div>

        {{-- Statuts --}}
        <div x-show="tab==='statuts'" x-cloak class="space-y-6">
            <livewire:settings.general-settings section="automation" />
            <livewire:settings.reference-list type="statuts" title="Statuts d'intervention" />
        </div>

        {{-- Facturation / déplacement --}}
        <div x-show="tab==='facturation'" x-cloak class="space-y-6">
            <livewire:settings.general-settings section="billing" />
        </div>

        {{-- Modèles (rapports / commentaires) — Livewire --}}
        <div x-show="tab==='modeles'" x-cloak class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <livewire:settings.reference-list type="rapport-types" title="Rapports types" />
            <livewire:settings.reference-list type="commentaire-types" title="Commentaires types" />
        </div>
    </div>
@endsection
