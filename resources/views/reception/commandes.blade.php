@extends('layouts.app')
@section('title', 'Commandes en cours')

@section('content')
    <x-page-header title="Commandes en cours" subtitle="Commandes fournisseurs en attente de réception" />
    <livewire:pending-commandes />
@endsection
