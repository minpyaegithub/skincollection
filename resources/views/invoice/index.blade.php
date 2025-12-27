@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
    <div class="container-fluid">
        @livewire('invoices-manager')
    </div>
@endsection
