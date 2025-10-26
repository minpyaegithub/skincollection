@extends('layouts.app')

@section('title', 'Appointments Calendar')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4"></div>

    {{-- Alert Messages --}}
    @include('common.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-body">
            @livewire('appointments-calendar')
        </div>
    </div>

</div>

@endsection
@section('scripts')

@endsection