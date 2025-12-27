@extends('layouts.app')

@section('title', 'Edit Invoice')

@section('content')
	<div class="container-fluid">
		<div class="alert alert-info">
			Invoice management now lives in the Livewire interface.
			<a href="{{ route('invoices.index') }}" class="alert-link">Return to Invoices</a>.
		</div>
	</div>
@endsection
