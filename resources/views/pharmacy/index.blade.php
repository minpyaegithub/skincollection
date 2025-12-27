@extends('layouts.app')

@section('title', 'Pharmacy')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-body">
            @livewire('pharmacies-manager')
        </div>
    </div>
</div>
@endsection
