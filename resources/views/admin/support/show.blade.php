@extends('admin.layout')
@section('title', 'Assistance '.$ticket->reference)

@section('content')
    <livewire:admin.support-thread :ticket="$ticket" />
@endsection
