@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        Expense editing is handled within the Livewire panel.
        <a href="{{ route('expense.index') }}" class="alert-link">Return to Expenses</a>.
    </div>
</div>
@endsection