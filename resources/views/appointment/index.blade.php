@extends('layouts.app')

@section('title', 'Appointments')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Appointments</h1>
    </div>

    @include('common.alert')

    <div class="card shadow-sm">
        <div class="card-body">
            @livewire('appointments-calendar')
        </div>
    </div>
</div>
@endsection
