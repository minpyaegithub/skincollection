@extends('layouts.app')

@section('title', 'Edit Treatment Package')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Treatment Package</h1>
            <a href="{{ route('treatment-packages.index') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>

        @include('common.alert')

        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('treatment-packages.update', $package) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $package->name) }}" required>
                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $package->description) }}</textarea>
                        @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Price</label>
                            <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $package->price) }}" required>
                            @error('price') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label>Sessions</label>
                            <input type="number" min="1" name="sessions" class="form-control" value="{{ old('sessions', $package->sessions) }}" required>
                            @error('sessions') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group col-md-4 d-flex align-items-center" style="padding-top: 32px;">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
@endsection
