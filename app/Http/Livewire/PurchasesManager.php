<?php

namespace App\Http\Livewire;

use App\Models\OutOfStock;
use App\Models\Pharmacy;
use App\Models\Purchase;
use App\Services\ClinicContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PurchasesManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $clinicId;
    public $clinicOptions = [];
    public $viewingAllClinics = false;
    public $isAdmin = false;

    public $purchaseId;
    public $pharmacyId;
    public $selling_price;
    public $net_price;
    public $qty;
    public $purchase_date;

    public $pharmacyOptions = [];

    public $isModalOpen = false;

    protected ?ClinicContext $clinicContext = null;

    protected $listeners = [
        'confirmPurchaseDeletion' => 'delete',
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
        } else {
            $user = auth()->user();
            $clinicContext = $this->resolveClinicContext();
            $clinicContext->initialize($user);
            $this->viewingAllClinics = $clinicContext->isAllClinicsSelection($user);
            $currentClinicId = $clinicContext->currentClinicId($user);
            $this->clinicId = $this->viewingAllClinics ? 'all' : ($currentClinicId ? (string) $currentClinicId : null);
        }

        $this->pharmacyId = null;
        $this->loadPharmacyOptions();
        $this->resetPage();
    }

    public function mount(ClinicContext $clinicContext): void
    {
        $this->clinicContext = $clinicContext;

        $user = auth()->user();
        $this->isAdmin = $user->isAdmin();

        if (!$this->isAdmin) {
            abort(403);
        }

        $clinicContext->initialize($user);

        $this->clinicOptions = $clinicContext->availableClinics($user)
            ->map(fn ($clinic) => ['id' => $clinic->id, 'name' => $clinic->name])
            ->toArray();

        $this->viewingAllClinics = $clinicContext->isAllClinicsSelection($user);
        $currentClinicId = $clinicContext->currentClinicId($user);

        $this->clinicId = $this->viewingAllClinics ? 'all' : ($currentClinicId ? (string) $currentClinicId : null);

        $this->purchase_date = today()->format('Y-m-d');

        $this->loadPharmacyOptions();
    }

    public function updatedClinicId($value): void
    {
        if (!$this->isAdmin) {
            return;
        }

        $user = auth()->user();
        $clinicContext = $this->resolveClinicContext();

        if ($value === 'all') {
            $clinicContext->setClinic($user, null);
            $this->viewingAllClinics = true;
        } else {
            $clinicId = $value ? (int) $value : null;
            $clinicContext->setClinic($user, $clinicId);
            $this->viewingAllClinics = false;
        }

        $this->pharmacyId = null;
        $this->loadPharmacyOptions();
        $this->resetPage();
    }

    public function updatedPharmacyId(): void
    {
        $this->resetValidation('pharmacyId');
    }

    public function openModal(?int $purchaseId = null): void
    {
        if ($this->isAdmin && $this->viewingAllClinics && !$purchaseId) {
            session()->flash('error', 'Select a clinic before creating a purchase.');
            return;
        }

        $this->resetValidation();

        if ($purchaseId) {
            $this->loadPurchase($purchaseId);
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

    public function savePurchase(): void
    {
        $validated = $this->validate($this->rules());

        $pharmacy = Pharmacy::with('clinic')->findOrFail($this->pharmacyId);
        $clinicId = $pharmacy->clinic_id;

        if (!$clinicId) {
            session()->flash('error', 'Selected pharmacy is not assigned to a clinic.');
            return;
        }

        if (!$this->viewingAllClinics && $this->getActiveClinicId() && $clinicId !== $this->getActiveClinicId()) {
            session()->flash('error', 'Pharmacy does not belong to the active clinic.');
            return;
        }

        $purchaseDate = $this->purchase_date
            ? Carbon::createFromFormat('Y-m-d', $this->purchase_date)->startOfDay()
            : today();

        $isNew = empty($this->purchaseId);

        DB::transaction(function () use ($clinicId, $purchaseDate) {
            $purchase = Purchase::updateOrCreate(
                ['id' => $this->purchaseId],
                [
                    'clinic_id' => $clinicId,
                    'phar_id' => $this->pharmacyId,
                    'selling_price' => $this->selling_price,
                    'net_price' => $this->net_price,
                    'qty' => $this->qty,
                    'created_time' => $purchaseDate,
                ]
            );

            $this->purchaseId = $purchase->id;

            $purchase->load('pharmacy');

            if ($purchase->pharmacy) {
                $this->syncInventory($purchase->pharmacy);
            }
        });

        session()->flash('success', $isNew ? 'Purchase recorded successfully.' : 'Purchase updated successfully.');

        $this->closeModal();
        $this->resetPage();
        $this->loadPharmacyOptions();
    }

    public function delete(int $purchaseId): void
    {
        $purchase = Purchase::with('pharmacy')->findOrFail($purchaseId);

        $pharmacy = $purchase->pharmacy;

        DB::transaction(function () use ($purchase) {
            $purchase->delete();
        });

        if ($pharmacy) {
            $this->syncInventory($pharmacy);
        }

        session()->flash('success', 'Purchase deleted successfully.');

        $this->resetPage();
        $this->loadPharmacyOptions();
    }

    public function render()
    {
        $purchases = $this->buildQuery()->paginate(10);

        return view('livewire.purchases-manager', [
            'purchases' => $purchases,
        ]);
    }

    protected function buildQuery()
    {
        $query = Purchase::with(['clinic', 'pharmacy'])->orderByDesc('created_time')->orderByDesc('created_at');

        $activeClinicId = $this->getActiveClinicId();

        if ($activeClinicId) {
            $query->forClinic($activeClinicId);
        }

        return $query;
    }

    protected function loadPurchase(int $purchaseId): void
    {
        $purchase = Purchase::with('pharmacy')->findOrFail($purchaseId);

        $this->purchaseId = $purchase->id;
        $this->pharmacyId = $purchase->phar_id;
        $this->selling_price = $purchase->selling_price;
        $this->net_price = $purchase->net_price;
        $this->qty = $purchase->qty;
        $this->purchase_date = optional($purchase->created_time)->format('Y-m-d') ?? today()->format('Y-m-d');

        $this->loadPharmacyOptions();
    }

    protected function resetForm(): void
    {
        $this->purchaseId = null;
        $this->pharmacyId = null;
        $this->selling_price = null;
        $this->net_price = null;
        $this->qty = null;
        $this->purchase_date = today()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'pharmacyId' => 'required|exists:pharmacies,id',
            'selling_price' => 'required|numeric|min:0',
            'net_price' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:1',
            'purchase_date' => 'required|date',
        ];
    }

    protected function getActiveClinicId(): ?int
    {
        if ($this->isAdmin && $this->viewingAllClinics) {
            return null;
        }

        if ($this->clinicId === null || $this->clinicId === '' || $this->clinicId === 'all') {
            return null;
        }

        return (int) $this->clinicId;
    }

    protected function loadPharmacyOptions(): void
    {
        $query = Pharmacy::with('clinic')->orderBy('name');

        $activeClinicId = $this->getActiveClinicId();

        if ($activeClinicId) {
            $query->forClinic($activeClinicId);
        }

        $this->pharmacyOptions = $query->get()
            ->map(function ($pharmacy) {
                $label = $pharmacy->name;

                if ($this->isAdmin && $this->viewingAllClinics) {
                    $label .= ' (' . optional($pharmacy->clinic)->name . ')';
                }

                return [
                    'id' => $pharmacy->id,
                    'name' => $label,
                ];
            })
            ->toArray();
    }

    protected function resolveClinicContext(): ClinicContext
    {
        if (!$this->clinicContext) {
            $this->clinicContext = app(ClinicContext::class);
        }

        return $this->clinicContext;
    }

    protected function syncInventory(Pharmacy $pharmacy): void
    {
        $latestPurchase = Purchase::where('phar_id', $pharmacy->id)
            ->orderByDesc('created_time')
            ->orderByDesc('created_at')
            ->first();

        if ($latestPurchase) {
            $pharmacy->update([
                'selling_price' => $latestPurchase->selling_price,
                'net_price' => $latestPurchase->net_price,
            ]);
        }

        $totalQty = Purchase::where('phar_id', $pharmacy->id)->sum('qty');

        $outOfStock = OutOfStock::firstOrNew(['phar_id' => $pharmacy->id]);
        $outOfStock->clinic_id = $pharmacy->clinic_id;
        $outOfStock->total = $totalQty;
        $outOfStock->sale = $outOfStock->sale ?? 0;
        $outOfStock->save();
    }
}
