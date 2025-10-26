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

    public function mount()
    {
        $this->clinic = Auth::user()->clinic;
        $this->loadStats();
    }

    public function loadStats()
    {
        $clinicId = $this->clinic->id;
        
        $this->stats = [
            'total_patients' => Patient::where('clinic_id', $clinicId)->count(),
            'total_users' => User::where('clinic_id', $clinicId)->count(),
            'total_appointments' => Appointment::where('clinic_id', $clinicId)->count(),
            'total_invoices' => Invoice::where('clinic_id', $clinicId)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard')
            ->layout('layouts.app');
    }
}
