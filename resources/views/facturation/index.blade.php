@extends('layouts.app')
@section('title', 'Facturation')

@section('content')
    <x-page-header :title="auth()->user()->society?->invoice_enabled ? 'Factures' : 'Facturation'"
        :subtitle="auth()->user()->society?->invoice_enabled ? 'Générez et consultez les factures PDF de vos interventions' : 'Suivez les interventions clôturées à facturer'" />

    <livewire:facturation />
@endsection
