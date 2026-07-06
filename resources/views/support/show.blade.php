@extends('layouts.app')
@section('title', 'Demande '.$ticket->reference)

@section('content')
    <livewire:support.ticket-thread :ticket="$ticket" />
@endsection
