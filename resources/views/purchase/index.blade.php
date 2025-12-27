@extends('layouts.app')

@section('title', 'Purchase List')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800">Purchases</h1>
        <a href="{{ route('purchase.export') }}" class="btn btn-sm btn-success">
            <i class="fas fa-file-export"></i> Export To Excel
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @livewire('purchases-manager')
        </div>
    </div>
</div>
@endsection
