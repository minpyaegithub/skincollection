<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @can('create-patients')
                <div class="col-6 mb-3">
                    <a href="{{ route('patients.index') }}" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> Add Patient
                    </a>
                </div>
                @endcan
                @can('create-appointments')
                <div class="col-6 mb-3">
                    <a href="{{ route('appointments.index') }}" class="btn btn-success btn-block">
                        <i class="fas fa-calendar-plus"></i> Book Appointment
                    </a>
                </div>
                @endcan
                @can('create-invoices')
                <div class="col-6 mb-3">
                    <a href="{{ route('invoices.index') }}" class="btn btn-warning btn-block">
                        <i class="fas fa-file-invoice"></i> Create Invoice
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>