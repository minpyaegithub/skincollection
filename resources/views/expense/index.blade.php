@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-body">
            @livewire('expenses-manager')
        </div>
    </div>
</div>
@endsection
