<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Clinic;
use App\Models\ClinicCounter;
use Illuminate\Support\Facades\Auth;

class ClinicManagement extends Component
{
    public $clinics = [];
    public $search = '';
    public $showModal = false;
    public $editingClinic = null;
    
    // Clinic form fields
    public $name = '';
    public $prefix = '';
    public $address = '';
    public $phone = '';
    public $email = '';
    public $status = 1;

    protected $rules = [
        'name' => 'required|string|max:255',
        'prefix' => 'required|string|max:10|unique:clinics,prefix',
        'address' => 'nullable|string',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'status' => 'boolean',
    ];

    public function mount()
    {
        $this->loadClinics();
    }

    public function loadClinics()
    {
        $query = Clinic::query();
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('prefix', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }
        
        $this->clinics = $query->orderBy('created_at', 'desc')->get();
    }

    public function updatedSearch()
    {
        $this->loadClinics();
    }

    public function showCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function showEditModal($clinicId)
    {
        $clinic = Clinic::findOrFail($clinicId);
        $this->editingClinic = $clinic;
        
        $this->name = $clinic->name;
        $this->prefix = $clinic->prefix;
        $this->address = $clinic->address;
        $this->phone = $clinic->phone;
        $this->email = $clinic->email;
        $this->status = $clinic->status;
        
        $this->showModal = true;
    }

    public function saveClinic()
    {
        $rules = $this->rules;
        
        // If editing, allow same prefix
        if ($this->editingClinic) {
            $rules['prefix'] = 'required|string|max:10|unique:clinics,prefix,' . $this->editingClinic->id;
        }
        
        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'prefix' => $this->prefix,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status,
        ];

        if ($this->editingClinic) {
            // Update existing clinic
            $this->editingClinic->update($data);
            session()->flash('message', 'Clinic updated successfully!');
        } else {
            // Create new clinic
            $clinic = Clinic::create($data);
            
            // Initialize counters for the new clinic
            $this->initializeClinicCounters($clinic);
            
            session()->flash('message', 'Clinic created successfully!');
        }

        $this->resetForm();
        $this->loadClinics();
    }

    public function deleteClinic($clinicId)
    {
        $clinic = Clinic::findOrFail($clinicId);
        
        // Check if clinic has users
        if ($clinic->users()->count() > 0) {
            session()->flash('error', 'Cannot delete clinic with existing users!');
            return;
        }
        
        $clinic->delete();
        session()->flash('message', 'Clinic deleted successfully!');
        $this->loadClinics();
    }

    public function initializeClinicCounters($clinic)
    {
        $counterTypes = ['patient', 'appointment', 'invoice', 'treatment'];
        
        foreach ($counterTypes as $type) {
            ClinicCounter::create([
                'clinic_id' => $clinic->id,
                'counter_type' => $type,
                'current_number' => 0,
                'prefix' => $clinic->prefix,
            ]);
        }
    }

    public function resetForm()
    {
        $this->editingClinic = null;
        $this->name = '';
        $this->prefix = '';
        $this->address = '';
        $this->phone = '';
        $this->email = '';
        $this->status = 1;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.clinic-management')
            ->layout('layouts.app');
    }
}
