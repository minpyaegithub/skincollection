<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Appointment;
use App\Models\AppointmentTime;
use Carbon\Carbon;

class AppointmentsCalendar extends Component
{
    public $date;
    public $appointmentsForDate = [];
    public $bookedTimes = [];
    public $allTimeSlots = [];

    // Form properties
    public $appointmentId;
    public $name;
    public $phone;
    public $description;
    public $status = 0;
    public $time = [];

    public $isModalOpen = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'description' => 'nullable|string',
        'status' => 'required|boolean',
        'time' => 'required|array|min:1', // This now refers to an array of appointment_time_ids
        'date' => 'required|date',
    ];

    public function mount()
    {
        $this->date = today()->format('Y-m-d');
        $this->allTimeSlots = AppointmentTime::all();
        $this->loadAppointmentsForDate();
    }

    public function updatedDate()
    {
        $this->loadAppointmentsForDate();
    }

    public function loadAppointmentsForDate()
    {
        $this->appointmentsForDate = Appointment::with('timeSlots')->whereDate('date', $this->date)->get();
        $this->bookedTimes = $this->appointmentsForDate
            ->pluck('timeSlots')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();
    }

    public function getAppointmentsForTime($timeSlot)
    {
        return $this->appointmentsForDate->filter(function ($appointment) use ($timeSlot) {
            // Check if any of the appointment's time slots match the given time slot's ID
            return $appointment->timeSlots->contains('id', $timeSlot->id);
        });
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function resetForm()
    {
        $this->appointmentId = null;
        $this->name = '';
        $this->phone = '';
        $this->description = '';
        $this->status = 0;
        $this->time = [];
    }

    public function edit($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);
        $this->appointmentId = $appointment->id;
        $this->name = $appointment->name;
        $this->phone = $appointment->phone;
        $this->description = $appointment->description;
        $this->status = $appointment->status;
        $this->time = $appointment->timeSlots->pluck('id')->toArray();
        $this->date = Carbon::parse($appointment->date)->format('Y-m-d');

        $this->isModalOpen = true;
    }

    public function saveAppointment()
    {
        $this->validate();

        $appointment = Appointment::updateOrCreate(
            ['id' => $this->appointmentId],
            [
                'name' => $this->name,
                'phone' => $this->phone,
                'description' => $this->description,
                'status' => $this->status,
                'date' => $this->date,
            ]
        );

        // Sync the many-to-many relationship
        $appointment->timeSlots()->sync($this->time);

        session()->flash('success', $this->appointmentId ? 'Appointment Updated Successfully.' : 'Appointment Created Successfully.');

        $this->closeModal();
        $this->loadAppointmentsForDate();
    }

    public function delete($appointmentId)
    {
        Appointment::findOrFail($appointmentId)->delete();
        session()->flash('success', 'Appointment Deleted Successfully.');
        $this->loadAppointmentsForDate();
    }

    public function render()
    {
        return view('livewire.appointments-calendar');
    }
}