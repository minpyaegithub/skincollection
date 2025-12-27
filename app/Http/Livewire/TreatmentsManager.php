<?php

namespace App\Http\Livewire;

use App\Models\Treatment;
use App\Services\ClinicContext;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TreatmentsManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $clinicId;
    public $clinicOptions = [];
    public $viewingAllClinics = false;
    public $isAdmin = false;

    public $statusFilter = 'all';
    public $search = '';

    public $treatmentId;
    public $formClinicId;
    public $name = '';
    public $description = '';
    public $price = '';
    public $durationMinutes = '';
    public $isActive = true;

    public $isModalOpen = false;

    protected ?ClinicContext $clinicContext = null;

    protected $listeners = [
        'clinicContextChanged' => 'handleClinicContextChanged',
    ];

    public function clinicContextChanged($payload = null): void
    {
        $this->handleClinicContextChanged($payload);
    }

    public function handleClinicContextChanged($payload = null): void
    {
        if (is_array($payload) && array_key_exists('clinicId', $payload)) {
            $clinicId = $payload['clinicId'];
            $this->viewingAllClinics = (bool) ($payload['viewingAllClinics'] ?? ($clinicId === null));
            $this->clinicId = $clinicId === null ? 'all' : (string) $clinicId;
            $this->formClinicId = $clinicId === null ? null : (int) $clinicId;
        } else {
            $this->syncClinicState();
        }

        $this->resetPage();
    }

    protected function syncClinicState(): void
    {
        $user = auth()->user();
        $this->isAdmin = $user->isAdmin();

        $clinicContext = $this->resolveClinicContext();
        $clinicContext->initialize($user);

        $this->clinicOptions = $clinicContext->availableClinics($user)
            ->map(fn ($clinic) => ['id' => $clinic->id, 'name' => $clinic->name])
            ->toArray();

        $this->viewingAllClinics = $clinicContext->isAllClinicsSelection($user);
        $currentClinicId = $clinicContext->currentClinicId($user);

        if ($this->isAdmin) {
            $this->clinicId = $this->viewingAllClinics ? 'all' : ($currentClinicId ? (string) $currentClinicId : null);
            $this->formClinicId = $this->viewingAllClinics ? null : $currentClinicId;
        } else {
            $this->clinicId = $currentClinicId ? (string) $currentClinicId : null;
            $this->formClinicId = $currentClinicId;
        }
    }


    public function mount(ClinicContext $clinicContext): void
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

        if ($this->isAdmin) {
            $this->clinicId = $this->viewingAllClinics ? 'all' : ($currentClinicId ? (string) $currentClinicId : null);
            $this->formClinicId = $this->viewingAllClinics ? null : $currentClinicId;
        } else {
            $this->clinicId = $currentClinicId ? (string) $currentClinicId : null;
            $this->formClinicId = $currentClinicId;
        }
    }

    public function updatedClinicId($value): void
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

        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openModal(?int $treatmentId = null): void
    {
        if ($this->isAdmin && $this->viewingAllClinics && !$treatmentId) {
            session()->flash('error', 'Select a clinic before creating a treatment.');
            return;
        }

        $this->resetValidation();

        if ($treatmentId) {
            $this->loadTreatment($treatmentId);
        } else {
            $this->resetForm();
        }

        $this->isModalOpen = true;
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function saveTreatment(): void
    {
        $validated = $this->validate($this->rules());

        $clinicId = $this->isAdmin
            ? (int) ($validated['formClinicId'] ?? 0)
            : $this->getActiveClinicId();

        if (!$clinicId) {
            session()->flash('error', 'Clinic selection is required.');
            return;
        }

        $isNew = empty($this->treatmentId);

        DB::transaction(function () use ($clinicId, $isNew) {
            $treatment = Treatment::updateOrCreate(
                ['id' => $this->treatmentId],
                [
                    'clinic_id' => $clinicId,
                    'name' => trim($this->name),
                    'description' => $this->description ?: null,
                    'price' => (float) $this->price,
                    'duration_minutes' => $this->durationMinutes !== '' ? (int) $this->durationMinutes : null,
                    'is_active' => (bool) $this->isActive,
                ]
            );

            $this->treatmentId = $treatment->id;
        });

        session()->flash('success', $isNew ? 'Treatment created successfully.' : 'Treatment updated successfully.');

        $this->closeModal();
        $this->resetForm();
        $this->resetPage();
    }

    public function delete(int $treatmentId): void
    {
        if (!$this->isAdmin) {
            abort(403);
        }

        $treatment = Treatment::findOrFail($treatmentId);
        $this->ensureTreatmentAccessible($treatment);

        $treatment->delete();

        session()->flash('success', 'Treatment deleted successfully.');
        $this->resetPage();
    }

    public function toggleStatus(int $treatmentId): void
    {
        $treatment = Treatment::findOrFail($treatmentId);
        $this->ensureTreatmentAccessible($treatment);

        $treatment->is_active = !$treatment->is_active;
        $treatment->save();

        session()->flash('success', sprintf('Treatment %s %s.', $treatment->name, $treatment->is_active ? 'activated' : 'deactivated'));
    }

    public function render()
    {
        $treatments = $this->buildQuery()->paginate(10);

        return view('livewire.treatments-manager', [
            'treatments' => $treatments,
        ]);
    }

    protected function buildQuery()
    {
        $query = Treatment::with('clinic')->orderBy('name');

        $activeClinicId = $this->getActiveClinicId();

        if ($activeClinicId) {
            $query->forClinic($activeClinicId);
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if ($this->search) {
            $query->where(function ($inner) {
                $inner->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $query;
    }

    protected function loadTreatment(int $treatmentId): void
    {
        $treatment = Treatment::findOrFail($treatmentId);
        $this->ensureTreatmentAccessible($treatment);

        $this->treatmentId = $treatment->id;
        $this->formClinicId = $treatment->clinic_id;
        $this->name = $treatment->name;
        $this->description = $treatment->description;
        $this->price = $treatment->price;
        $this->durationMinutes = $treatment->duration_minutes;
        $this->isActive = (bool) $treatment->is_active;
    }

    protected function resetForm(): void
    {
        $this->treatmentId = null;
        $this->formClinicId = $this->isAdmin && $this->viewingAllClinics ? null : $this->getActiveClinicId();
        $this->name = '';
        $this->description = '';
        $this->price = '';
        $this->durationMinutes = '';
        $this->isActive = true;
        $this->resetValidation();
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'durationMinutes' => 'nullable|integer|min:0',
            'isActive' => 'boolean',
        ];

        if ($this->isAdmin) {
            $rules['formClinicId'] = 'required|exists:clinics,id';
        }

        return $rules;
    }

    protected function getActiveClinicId(): ?int
    {
        if ($this->isAdmin && $this->viewingAllClinics) {
            return null;
        }

        if ($this->clinicId === null || $this->clinicId === '') {
            return null;
        }

        return (int) $this->clinicId;
    }

    protected function ensureTreatmentAccessible(Treatment $treatment): void
    {
        if ($this->isAdmin) {
            return;
        }

        $activeClinicId = $this->getActiveClinicId();

        if (!$activeClinicId || $treatment->clinic_id !== $activeClinicId) {
            abort(403);
        }
    }

    protected function resolveClinicContext(): ClinicContext
    {
        if (!$this->clinicContext) {
            $this->clinicContext = app(ClinicContext::class);
        }

        return $this->clinicContext;
    }
}
