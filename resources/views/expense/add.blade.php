@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">
        Expense creation now happens within the Livewire manager.
        <a href="{{ route('expense.index') }}" class="alert-link">Return to Expenses</a>.
    </div>
</div>
@endsection