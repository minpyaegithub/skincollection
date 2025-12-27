<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use App\Services\ClinicContext;

class Dashboard extends Component
{
    public $stats = [];
    public $clinic;
    public $debug = '';
    public $viewingAllClinics = false;
    public $recentPatients = [];

    public function mount(ClinicContext $clinicContext)
    {
        try {
            $user = Auth::user();

            $clinicContext->initialize($user);

            $this->clinic = $clinicContext->currentClinic($user);
            $this->viewingAllClinics = $clinicContext->isAllClinicsSelection($user);

            $clinicIds = [];

            if ($this->clinic) {
                $clinicIds = [$this->clinic->id];
            } else {
                $clinicIds = $clinicContext->availableClinics($user)->pluck('id')->all();
            }

            $this->loadStats($clinicIds);
        } catch (\Throwable $e) {
            $this->debug = 'Error: ' . $e->getMessage();
        }
    }

    public function loadStats(array $clinicIds)
    {
        $patientQuery = Patient::query();
        $userQuery = User::query();
        $appointmentQuery = Appointment::query();
        $invoiceQuery = Invoice::query();

        if (!empty($clinicIds)) {
            $patientQuery->whereIn('clinic_id', $clinicIds);
            $userQuery->whereIn('clinic_id', $clinicIds);
            $appointmentQuery->whereIn('clinic_id', $clinicIds);
            $invoiceQuery->whereIn('clinic_id', $clinicIds);
        }

        $this->stats = [
            'total_patients' => $patientQuery->count(),
            'total_users' => $userQuery->count(),
            'total_appointments' => $appointmentQuery->count(),
            'total_invoices' => $invoiceQuery->count(),
        ];

        $this->recentPatients = Patient::query()
            ->when(!empty($clinicIds), fn ($query) => $query->whereIn('clinic_id', $clinicIds))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard')
            ->layout('layouts.app');
    }
}
