@extends('layouts.app')
@section('title', 'Facturation')

@section('content')
    <x-page-header title="Factures" subtitle="Générez et consultez les factures PDF de vos interventions" />

    <livewire:facturation />
@endsection
