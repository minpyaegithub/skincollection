@extends('layouts.app')

@section('title', 'Edit Pharmacy')

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        Pharmacy management now lives in the Livewire interface.
        <a href="{{ route('pharmacy.index') }}" class="alert-link">Return to Pharmacy</a>.
    </div>
</div>
@endsection