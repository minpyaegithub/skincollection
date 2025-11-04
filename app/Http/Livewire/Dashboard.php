<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $stats = [];
    public $clinic;
    public $debug = '';

    public function mount()
    {
        try {
            $this->clinic = Auth::user()->clinic;
            if ($this->clinic) {
                $this->loadStats();
            } else {
                $this->debug = 'No clinic found for user.';
            }
        } catch (\Throwable $e) {
            $this->debug = 'Error: ' . $e->getMessage();
        }
    }

    public function loadStats()
    {
        $clinicId = $this->clinic->id;

        $this->stats = [
            'total_patients' => Patient::where('clinic_id', $clinicId)->count(),
            'total_users' => User::where('clinic_id', $clinicId)->count(),
            'total_appointments' => Appointment::whereIn(
                'phone',
                Patient::where('clinic_id', $clinicId)->pluck('phone')
            )->count(),
            'total_invoices' => Invoice::whereIn(
                'patient_id',
                Patient::where('clinic_id', $clinicId)->pluck('id')
            )->count(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard')
            ->layout('layouts.app');
    }
}
