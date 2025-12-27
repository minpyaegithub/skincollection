@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Patient Record</h5>
                <div>
                    <a href="{{ route('record.edit', $record->id) }}" class="btn btn-primary btn-sm">Edit</a>
                    <a href="{{ route('record.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Patient:</strong>
                        <div>{{ $patient->name ?? ('#' . $patient->id) }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Date:</strong>
                        <div>{{ optional($record->record_date)->format('d-m-Y') }}</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-3">
                        <strong>Title:</strong>
                        <div>{{ $record->title }}</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <div style="white-space: pre-wrap;">{{ $record->description }}</div>
                    </div>
                </div>
            </div>

            <hr>

            <h6 class="mb-3">Images</h6>

            @if(($photos ?? collect())->count() === 0)
                <div class="text-muted">No images attached to this record.</div>
            @else
                <div class="row">
                    @foreach($photos as $p)
                        <div class="col-6 col-md-3 mb-3">
                            <a href="{{ $p['url'] }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $p['url'] }}" class="img-fluid img-thumbnail" alt="Record photo" />
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
