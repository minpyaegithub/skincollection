<?php

namespace App\Http\Livewire;

use App\Models\Appointment;
use App\Models\AppointmentTime;
use App\Services\ClinicContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AppointmentsCalendar extends Component
{
    public $date;
    public $appointmentsForDate;
    public $bookedTimes = [];
    public $allTimeSlots = [];
    public $clinicId;
    public $clinicOptions = [];

    // Form properties
    public $appointmentId;
    public $name;
    public $phone;
    public $description;
    public $status = 0;
    public $time = [];
    public $formClinicId;

    public $isModalOpen = false;
    public $isAdmin = false;
    public $viewingAllClinics = false;

    protected ?ClinicContext $clinicContext = null;
    protected $listeners = [
        'clinicContextChanged' => 'handleClinicContextChanged',
    ];

    // Livewire v3 will call this when targeted via emitTo('appointments-calendar', 'clinicContextChanged', ...)
    // and it's also compatible as an event handler if the global dispatch is received.
    public function clinicContextChanged($payload = null): void
    {
        $this->handleClinicContextChanged($payload);
    }

    public function mount(ClinicContext $clinicContext)
    {
        $this->clinicContext = $clinicContext;

        $user = auth()->user();

        $this->isAdmin = $user->isAdmin();

        $clinicContext->initialize($user);

        $this->clinicOptions = $clinicContext->availableClinics($user)
            ->map(fn ($clinic) => ['id' => $clinic->id, 'name' => $clinic->name])
            ->toArray();
        $this->viewingAllClinics = $clinicContext->isAllClinicsSelection($user);
        $currentClinicId = $clinicContext->currentClinicId($user);

        $this->clinicId = $this->viewingAllClinics ? 'all' : ($currentClinicId ? (string) $currentClinicId : null);
        $this->formClinicId = $this->viewingAllClinics ? null : $currentClinicId;

        $this->date = today()->format('Y-m-d');
        $this->appointmentsForDate = collect();
        $this->allTimeSlots = AppointmentTime::orderBy('time')->get();
        $this->loadAppointmentsForDate();
    }

    public function updatedDate()
    {
        $this->loadAppointmentsForDate();
    }

    /**
     * Livewire action: invoked explicitly from the date input.
     * (Calling lifecycle hooks directly from Blade is not allowed in Livewire v3.)
     */
    public function changeDate($value): void
    {
        $this->date = $value;
        $this->loadAppointmentsForDate();
    }

    public function updatedClinicId($value)
    {
        if (!$this->isAdmin) {
            $userClinicId = optional(auth()->user())->clinic_id;
            $this->clinicId = $userClinicId ? (string) $userClinicId : null;
            return;
        }

        $user = auth()->user();
        $clinicContext = $this->resolveClinicContext();

        if ($value === 'all') {
            $clinicContext->setClinic($user, null);
            $this->viewingAllClinics = true;
            $this->formClinicId = null;
        } else {
            $clinicId = $value ? (int) $value : null;
            $clinicContext->setClinic($user, $clinicId);
            $this->viewingAllClinics = false;
            $this->formClinicId = $clinicId;
        }

        $this->loadAppointmentsForDate();
    }

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'time' => 'required|array|min:1',
            'date' => 'required|date',
        ];

        if ($this->isAdmin) {
            $rules['formClinicId'] = 'required|exists:clinics,id';
        }

        return $rules;
    }

    protected function getActiveClinicId(): ?int
    {
        if ($this->clinicId === 'all') {
            return null;
        }

        if ($this->isAdmin && $this->viewingAllClinics) {
            return null;
        }

        if ($this->clinicId === null || $this->clinicId === '') {
            return null;
        }

        return (int) $this->clinicId;
    }

    public function loadAppointmentsForDate()
    {
        $activeClinicId = $this->getActiveClinicId();

        $query = Appointment::with(['timeSlots', 'clinic'])
            ->whereDate('date', $this->date);

        if ($activeClinicId !== null) {
            $query->forClinic($activeClinicId);
        } elseif (!$this->isAdmin || !$this->viewingAllClinics) {
            $this->appointmentsForDate = collect();
            $this->bookedTimes = [];
            return;
        }

        $this->appointmentsForDate = $query->orderBy('date')->get();

        $this->bookedTimes = $this->appointmentsForDate
            ->pluck('timeSlots')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();
    }

    public function getAppointmentsForTime($timeSlot)
    {
        return $this->appointmentsForDate->filter(fn ($appointment) => $appointment->timeSlots->contains('id', $timeSlot->id));
    }

    public function openModal()
    {
        if ($this->isAdmin && $this->viewingAllClinics) {
            session()->flash('error', 'Select a clinic before creating appointments.');
            return;
        }

        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    protected function resetForm()
    {
        $this->appointmentId = null;
        $this->name = '';
        $this->phone = '';
        $this->description = '';
        $this->status = 0;
        $this->time = [];
        $this->formClinicId = $this->isAdmin && $this->viewingAllClinics ? null : $this->getActiveClinicId();
    }

    public function edit($appointmentId)
    {
        if ($this->isAdmin && $this->viewingAllClinics) {
            session()->flash('error', 'Select a clinic before editing appointments.');
            return;
        }

        $appointment = Appointment::with(['timeSlots', 'clinic'])->findOrFail($appointmentId);

        $this->ensureAppointmentAccessible($appointment);

        $this->appointmentId = $appointment->id;
        $this->name = $appointment->name;
        $this->phone = $appointment->phone;
        $this->description = $appointment->description;
        $this->status = (int) $appointment->status;
        $this->time = $appointment->timeSlots->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        $this->date = Carbon::parse($appointment->date)->format('Y-m-d');
        $this->formClinicId = $appointment->clinic_id;

        $this->isModalOpen = true;
    }

    public function saveAppointment()
    {
        $validated = $this->validate();

    $clinicId = $this->isAdmin ? (int) $validated['formClinicId'] : $this->getActiveClinicId();

        if (!$clinicId) {
            session()->flash('error', 'Clinic selection is required.');
            return;
        }

    $timeSlotIds = array_map('intval', $this->time);
    $isNewAppointment = empty($this->appointmentId);

        DB::transaction(function () use ($clinicId, $timeSlotIds) {
            $appointment = Appointment::updateOrCreate(
                ['id' => $this->appointmentId],
                [
                    'name' => $this->name,
                    'phone' => $this->phone,
                    'description' => $this->description,
                    'status' => $this->status,
                    'date' => $this->date,
                    'clinic_id' => $clinicId,
                    'time' => $this->compileLegacyTimeColumn($timeSlotIds),
                ]
            );

            $appointment->timeSlots()->sync($timeSlotIds);

            $this->appointmentId = $appointment->id;
        });

        session()->flash('success', $isNewAppointment ? 'Appointment Created Successfully.' : 'Appointment Updated Successfully.');

        $this->closeModal();
        $this->loadAppointmentsForDate();
    }

    public function delete($appointmentId)
    {
        if ($this->isAdmin && $this->viewingAllClinics) {
            session()->flash('error', 'Select a clinic before deleting appointments.');
            return;
        }

        $appointment = Appointment::findOrFail($appointmentId);

        $this->ensureAppointmentAccessible($appointment);

        $appointment->delete();

        session()->flash('success', 'Appointment Deleted Successfully.');
        $this->loadAppointmentsForDate();
    }

    protected function ensureAppointmentAccessible(Appointment $appointment): void
    {
        if ($this->isAdmin) {
            return;
        }

        $activeClinicId = $this->getActiveClinicId();

        if (!$activeClinicId || $appointment->clinic_id !== $activeClinicId) {
            abort(403);
        }
    }

    protected function compileLegacyTimeColumn(array $timeSlotIds): string
    {
        return AppointmentTime::whereIn('id', $timeSlotIds)
            ->orderBy('time')
            ->pluck('time')
            ->implode(',');
    }

    protected function resolveClinicContext(): ClinicContext
    {
        if (!$this->clinicContext) {
            $this->clinicContext = app(ClinicContext::class);
        }

        return $this->clinicContext;
    }

    public function render()
    {
        $this->syncWithClinicContext();
        return view('livewire.appointments-calendar');
    }

    public function handleClinicContextChanged($payload = null): void
    {
        // Prefer the payload (fast + avoids relying on session rehydration timing),
        // then fall back to reading from session via ClinicContext.
        if (is_array($payload) && array_key_exists('viewingAllClinics', $payload)) {
            $this->viewingAllClinics = (bool) $payload['viewingAllClinics'];
        }

        if (is_array($payload) && array_key_exists('clinicId', $payload)) {
            $clinicId = $payload['clinicId'];

            $this->clinicId = $clinicId === null ? 'all' : (string) $clinicId;
            $this->formClinicId = $clinicId === null ? null : (int) $clinicId;

            $this->loadAppointmentsForDate();

            return;
        }

        $this->syncWithClinicContext(true);
    }

    protected function syncWithClinicContext(bool $forceRefresh = false): void
    {
        $user = auth()->user();
        $clinicContext = $this->resolveClinicContext();
        $clinicContext->initialize($user);

        $shouldViewAll = $clinicContext->isAllClinicsSelection($user);
        $currentClinicId = $clinicContext->currentClinicId($user);

        $targetClinicId = $shouldViewAll ? 'all' : ($currentClinicId ? (string) $currentClinicId : null);

        if ($this->clinicId !== $targetClinicId || $forceRefresh) {
            $this->clinicId = $targetClinicId;
            $this->viewingAllClinics = $shouldViewAll;
            $this->formClinicId = $shouldViewAll ? null : $currentClinicId;
            $this->loadAppointmentsForDate();
        } else {
            $this->viewingAllClinics = $shouldViewAll;
            if (!$shouldViewAll && $currentClinicId && $this->formClinicId !== $currentClinicId) {
                $this->formClinicId = $currentClinicId;
            }

            if ($forceRefresh) {
                $this->loadAppointmentsForDate();
            }
        }
    }
}