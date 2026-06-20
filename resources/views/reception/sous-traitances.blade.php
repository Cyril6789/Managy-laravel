@extends('layouts.app')
@section('title', 'Sous-traitance en cours')

@section('content')
    <x-page-header title="Sous-traitance en cours" subtitle="Sous-traitances en attente de retour" />
    <livewire:pending-sous-traitances />
@endsection
