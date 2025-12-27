<?php

namespace App\Http\Livewire;

use App\Models\OutOfStock;
use App\Models\Pharmacy;
use App\Services\ClinicContext;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PharmaciesManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $clinicId;
    public $clinicOptions = [];
    public $viewingAllClinics = false;
    public $isAdmin = false;

    public $pharmacyId;
    public $name;
    public $formClinicId;

    public $isModalOpen = false;

    protected ?ClinicContext $clinicContext = null;

    protected $listeners = [
        'confirmPharmacyDeletion' => 'delete',
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

    public function mount(ClinicContext $clinicContext): void
    {
        $this->clinicContext = $clinicContext;
        $this->syncClinicState();
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
        $this->dispatch('refreshClinicSwitcher');
    }

    public function openModal(?int $pharmacyId = null): void
    {
        if ($this->isAdmin && $this->viewingAllClinics && !$pharmacyId) {
            session()->flash('error', 'Select a clinic before creating a pharmacy item.');
            return;
        }

        $this->resetValidation();

        if ($pharmacyId) {
            $this->loadPharmacy($pharmacyId);
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

    public function savePharmacy(): void
    {
        $validated = $this->validate($this->rules());

        $clinicId = $this->isAdmin ? (int) ($validated['formClinicId'] ?? 0) : $this->getActiveClinicId();

        if (!$clinicId) {
            session()->flash('error', 'Clinic selection is required.');
            return;
        }

        $isNew = empty($this->pharmacyId);

        DB::transaction(function () use ($clinicId, $isNew) {
            $pharmacy = Pharmacy::updateOrCreate(
                ['id' => $this->pharmacyId],
                [
                    'name' => $this->name,
                    'clinic_id' => $clinicId,
                ]
            );

            $this->pharmacyId = $pharmacy->id;

            $this->syncInventory($pharmacy, $clinicId, $isNew);
        });

        session()->flash('success', $isNew ? 'Pharmacy item created successfully.' : 'Pharmacy item updated successfully.');

        $this->closeModal();
        $this->resetPage();
    }

    public function delete(int $pharmacyId): void
    {
        if (!$this->isAdmin) {
            abort(403);
        }

        $pharmacy = Pharmacy::findOrFail($pharmacyId);
        $this->ensurePharmacyAccessible($pharmacy);

        $pharmacy->delete();

        session()->flash('success', 'Pharmacy item deleted successfully.');

        $this->resetPage();
    }

    public function render()
    {
        $pharmacies = $this->buildQuery()->paginate(10);

        return view('livewire.pharmacies-manager', [
            'pharmacies' => $pharmacies,
        ]);
    }

    protected function buildQuery()
    {
        $query = Pharmacy::with(['clinic', 'stockSummary'])->orderBy('name');

        $activeClinicId = $this->getActiveClinicId();

        if ($activeClinicId) {
            $query->forClinic($activeClinicId);
        }

        return $query;
    }

    protected function loadPharmacy(int $pharmacyId): void
    {
        $pharmacy = Pharmacy::findOrFail($pharmacyId);
        $this->ensurePharmacyAccessible($pharmacy);

        $this->pharmacyId = $pharmacy->id;
        $this->name = $pharmacy->name;
        $this->formClinicId = $pharmacy->clinic_id;
    }

    protected function resetForm(): void
    {
        $this->pharmacyId = null;
        $this->name = '';
        $this->formClinicId = $this->isAdmin && $this->viewingAllClinics ? null : $this->getActiveClinicId();
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
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

    protected function ensurePharmacyAccessible(Pharmacy $pharmacy): void
    {
        if ($this->isAdmin) {
            return;
        }

        $activeClinicId = $this->getActiveClinicId();

        if (!$activeClinicId || $pharmacy->clinic_id !== $activeClinicId) {
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

    protected function syncClinicState(): void
    {
        $clinicContext = $this->resolveClinicContext();
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
    }

    protected function syncInventory(Pharmacy $pharmacy, int $clinicId, bool $isNew): void
    {
        $outOfStock = OutOfStock::firstOrNew(['phar_id' => $pharmacy->id]);

        if (!$outOfStock->exists || $isNew) {
            $outOfStock->total = $outOfStock->total ?? 0;
            $outOfStock->sale = $outOfStock->sale ?? 0;
        }

        $outOfStock->clinic_id = $clinicId;
        $outOfStock->save();
    }
}
