<div>
    <div class="container-fluid">
        @if($clinic || $viewingAllClinics)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Dashboard - {{ $viewingAllClinics ? 'All Clinics' : $clinic->name }}</h3>
                        </div>
                        <div class="card-body">
                            <!-- Stats Cards -->
                            <div class="row">
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h3>{{ $stats['total_patients'] ?? 0 }}</h3>
                                            <p>Total Patients</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <a href="{{ route('patients.index') }}" class="small-box-footer">
                                            More info <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h3>{{ $stats['total_users'] ?? 0 }}</h3>
                                            <p>Total Users</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <a href="{{ route('user-management.index') }}" class="small-box-footer">
                                            More info <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-warning">
                                        <div class="inner">
                                            <h3>{{ $stats['total_appointments'] ?? 0 }}</h3>
                                            <p>Total Appointments</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                        <a href="{{ route('appointments.index') }}" class="small-box-footer">
                                            More info <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-danger">
                                        <div class="inner">
                                            <h3>{{ $stats['total_invoices'] ?? 0 }}</h3>
                                            <p>Total Invoices</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-file-invoice"></i>
                                        </div>
                                        <a href="{{ route('invoices.index') }}" class="small-box-footer">
                                            More info <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Activity -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Recent Patients</h3>
                                        </div>
                                        <div class="card-body">
                                            @forelse($recentPatients as $patient)
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="mr-3">
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $patient->getPatientFullName() }}</h6>
                                                        <small class="text-muted">{{ $patient->created_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-muted">No recent patients</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                @include('livewire.partials.quick-actions')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="card-title">Welcome, {{ Auth::user()->first_name }}!</h4>
                    <p class="card-text">You are not currently assigned to a clinic. Please navigate using the sidebar or contact an administrator.</p>
                    @hasanyrole('Admin|admin')
                        <a href="{{ route('clinics.index') }}" class="btn btn-primary">Manage Clinics</a>
                    @endhasanyrole
                </div>
            </div>
        @endif
    </div>
    <div class="container mt-5">
        <h1>Dashboard Component Loaded</h1>
        @if($debug)
            <div class="alert alert-danger">{{ $debug }}</div>
        @endif
        @if($stats && count($stats))
            <ul>
                <li>Total Patients: {{ $stats['total_patients'] ?? 'N/A' }}</li>
                <li>Total Users: {{ $stats['total_users'] ?? 'N/A' }}</li>
                <li>Total Appointments: {{ $stats['total_appointments'] ?? 'N/A' }}</li>
                <li>Total Invoices: {{ $stats['total_invoices'] ?? 'N/A' }}</li>
            </ul>
        @else
            <div class="alert alert-warning">No stats available.</div>
        @endif
    </div>
</div>
