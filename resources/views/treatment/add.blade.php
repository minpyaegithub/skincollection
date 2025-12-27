@extends('layouts.app')

@section('title', 'Treatments')

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        Treatment creation now happens within the Livewire manager.
        <a href="{{ route('treatment.index') }}" class="alert-link">Return to Treatments</a>.
    </div>
</div>
@endsection