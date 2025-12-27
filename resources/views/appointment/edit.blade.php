@extends('layouts.app')

@section('title', 'Appointments')

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        The appointment editor is now managed via the Livewire calendar.
        <a href="{{ route('appointments.index') }}" class="alert-link">Return to Appointments</a>.
    </div>
</div>
@endsection