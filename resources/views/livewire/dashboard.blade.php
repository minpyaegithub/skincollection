<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Dashboard - {{ $clinic->name }}</h3>
                    </div>
                    <div class="card-body">
                        <!-- Stats Cards -->
                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3>{{ $stats['total_patients'] }}</h3>
                                        <p>Total Patients</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <a href="#" class="small-box-footer">
                                        More info <i class="fas fa-arrow-circle-right"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3>{{ $stats['total_users'] }}</h3>
                                        <p>Total Users</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <a href="#" class="small-box-footer">
                                        More info <i class="fas fa-arrow-circle-right"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3>{{ $stats['total_appointments'] }}</h3>
                                        <p>Total Appointments</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <a href="#" class="small-box-footer">
                                        More info <i class="fas fa-arrow-circle-right"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3>{{ $stats['total_invoices'] }}</h3>
                                        <p>Total Invoices</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-file-invoice"></i>
                                    </div>
                                    <a href="#" class="small-box-footer">
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
                                        @php
                                            $recentPatients = \App\Models\Patient::where('clinic_id', $clinic->id)
                                                ->orderBy('created_at', 'desc')
                                                ->limit(5)
                                                ->get();
                                        @endphp
                                        
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
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Quick Actions</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <a href="{{ route('patients.index') }}" class="btn btn-primary btn-block">
                                                    <i class="fas fa-user-plus"></i> Add Patient
                                                </a>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <a href="{{ route('appointments.index') }}" class="btn btn-success btn-block">
                                                    <i class="fas fa-calendar-plus"></i> Book Appointment
                                                </a>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <a href="{{ route('invoices.index') }}" class="btn btn-warning btn-block">
                                                    <i class="fas fa-file-invoice"></i> Create Invoice
                                                </a>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <a href="{{ route('report.index') }}" class="btn btn-info btn-block">
                                                    <i class="fas fa-chart-bar"></i> View Reports
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
