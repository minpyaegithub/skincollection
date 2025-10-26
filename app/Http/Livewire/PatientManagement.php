<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Patient;
use App\Models\Clinic;
use App\Services\S3Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PatientManagement extends Component
{
    use WithFileUploads;

    public $patients;
    public $clinic;
    public $search = '';
    public $showModal = false;
    public $editingPatient = null;
    
    // Patient form fields
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone = '';
    public $gender = '';
    public $age = '';
    public $address = '';
    public $weight = '';
    public $feet = '';
    public $inches = '';
    public $disease = '';
    public $photos = [];
    public $uploadedPhotos = [];

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'gender' => 'required|in:male,female,other',
        'age' => 'nullable|integer|min:0|max:150',
        'address' => 'nullable|string',
        'weight' => 'nullable|numeric|min:0',
        'feet' => 'nullable|integer|min:0|max:10',
        'inches' => 'nullable|integer|min:0|max:11',
        'disease' => 'nullable|string',
        'photos.*' => 'nullable|image|max:10240', // 10MB max
    ];

    public function mount()
    {
        $this->clinic = Auth::user()->clinic;
        $this->loadPatients();
    }

    public function loadPatients()
    {
        $query = Patient::where('clinic_id', $this->clinic->id);
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }
        
        $this->patients = $query->orderBy('created_at', 'desc')->get();
    }

    public function updatedSearch()
    {
        $this->loadPatients();
    }

    public function showCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function showEditModal($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        $this->editingPatient = $patient;
        
        $this->first_name = $patient->first_name;
        $this->last_name = $patient->last_name;
        $this->email = $patient->email;
        $this->phone = $patient->phone;
        $this->gender = $patient->gender;
        $this->age = $patient->age;
        $this->address = $patient->address;
        $this->weight = $patient->weight;
        $this->feet = $patient->feet;
        $this->inches = $patient->inches;
        $this->disease = $patient->disease;
        $this->uploadedPhotos = $patient->photo ? json_decode($patient->photo, true) : [];
        
        $this->showModal = true;
    }

    public function savePatient()
    {
        $this->validate();

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'age' => $this->age,
            'address' => $this->address,
            'weight' => $this->weight,
            'feet' => $this->feet,
            'inches' => $this->inches,
            'disease' => $this->disease,
            'clinic_id' => $this->clinic->id,
            'token' => Str::random(32),
        ];

        // Calculate BMI if weight and height are provided
        if ($this->weight && $this->feet && $this->inches) {
            $heightInMeters = (($this->feet * 12) + $this->inches) * 0.0254;
            $data['BMI'] = round($this->weight / ($heightInMeters * $heightInMeters), 2);
        }

        // Handle photo uploads
        if ($this->photos) {
            $uploadedPhotos = S3Service::uploadPatientPhotos($this->photos, $this->clinic->id);
            $data['photo'] = json_encode($uploadedPhotos);
        }

        if ($this->editingPatient) {
            // Update existing patient
            $this->editingPatient->update($data);
            session()->flash('message', 'Patient updated successfully!');
        } else {
            // Create new patient
            Patient::create($data);
            session()->flash('message', 'Patient created successfully!');
        }

        $this->resetForm();
        $this->loadPatients();
    }

    public function deletePatient($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        
        // Delete photos from S3
        if ($patient->photo) {
            $photos = json_decode($patient->photo, true);
            foreach ($photos as $photo) {
                S3Service::delete($photo);
            }
        }
        
        $patient->delete();
        session()->flash('message', 'Patient deleted successfully!');
        $this->loadPatients();
    }

    public function resetForm()
    {
        $this->editingPatient = null;
        $this->first_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->phone = '';
        $this->gender = '';
        $this->age = '';
        $this->address = '';
        $this->weight = '';
        $this->feet = '';
        $this->inches = '';
        $this->disease = '';
        $this->photos = [];
        $this->uploadedPhotos = [];
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.patient-management')
            ->layout('layouts.app');
    }
}
