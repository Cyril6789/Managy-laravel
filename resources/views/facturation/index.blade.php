@extends('layouts.app')
@section('title', 'Facturation')

@section('content')
    <x-page-header title="Facturation" subtitle="Interventions clôturées en attente de facturation" />

    <livewire:facturation />
@endsection
