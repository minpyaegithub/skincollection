@extends('layouts.app')

@section('title', 'Treatments')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-body">
            @livewire('treatments-manager')
        </div>
    </div>
</div>
@endsection
