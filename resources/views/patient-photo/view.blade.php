@extends('layouts.app')

@section('title', 'Patient Photo')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Patient Photo</h6>
                <div>
                    <a href="{{ route('photo.edit', ['photo' => $photo->id]) }}" class="btn btn-primary btn-sm">Edit</a>
                    <a href="{{ route('photo.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Patient:</strong>
                    <div>{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Date:</strong>
                    <div>{{ optional($photo->created_at)->format('d-m-Y') }}</div>
                </div>

                <div class="col-12 mb-3">
                    <strong>Note:</strong>
                    <div style="white-space: pre-wrap;">{{ $photo->description }}</div>
                </div>
            </div>

            <hr>

            <h6 class="mb-3">Images</h6>
            @if(($photos ?? collect())->count() === 0)
                <div class="text-muted">No images attached.</div>
            @else
                <div class="row">
                    @foreach($photos as $p)
                        <div class="col-6 col-md-3 mb-3">
                            <a href="{{ $p['url'] }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $p['url'] }}" class="img-fluid img-thumbnail" alt="Photo" />
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
