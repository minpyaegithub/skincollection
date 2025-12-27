@extends('layouts.app')

@section('title', 'Treatment Packages')

@section('content')
    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Treatment Packages</h1>
            <div>
                <a href="{{ route('treatment-packages.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Add New
                </a>
            </div>
        </div>

        {{-- Alert Messages --}}
        @include('common.alert')

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Packages</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tbl_treatment_packages" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Sessions</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($packages as $package)
                            <tr>
                                <td>{{ $package->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($package->description, 60) ?: '—' }}</td>
                                <td>{{ number_format((float) $package->price, 2) }}</td>
                                <td>{{ $package->sessions }}</td>
                                <td>
                                    <span class="badge badge-pill {{ $package->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $package->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ optional($package->created_at)->format('d-m-Y') ?? '—' }}</td>
                                <td style="display:flex; gap: .5rem;">
                                    <a href="{{ route('treatment-packages.edit', $package) }}" class="btn btn-sm btn-primary">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('treatment-packages.destroy', $package) }}" onsubmit="return confirm('Delete this package?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            if (!$.fn || !$.fn.DataTable) return;
            $('#tbl_treatment_packages').DataTable({
                "lengthChange": true,
                "info": true,
                "searching": true,
                "aaSorting": []
            });
        });
    </script>
@endsection
