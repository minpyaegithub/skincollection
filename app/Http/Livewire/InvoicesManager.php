<?php

namespace App\Http\Livewire;

use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OutOfStock;
use App\Models\Patient;
use App\Models\Pharmacy;
use App\Models\TreatmentPackage;
use App\Services\ClinicContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class InvoicesManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $clinicId;
    public $clinicOptions = [];
    public $viewingAllClinics = false;
    public $isAdmin = false;

    public $typeFilter = 'all';
    public $statusFilter = 'all';

    public $patients = [];
    public $treatments = [];
    public $pharmacies = [];

    public $invoiceNumber;
    public $invoiceDate;
    public $dueDate;
    public $status = 'sent';
    public $notes;
    public $patientId;

    public $treatmentLines = [];
    public $saleLines = [];

    public $subtotal = 0;
    public $totalAmount = 0;

    public $isModalOpen = false;
    public $isViewingInvoice = false;
    public $viewingInvoice;

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
        if (! $this->isAdmin) {
            return;
        }

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

        $this->resetForm();
        $this->loadSelectableData();
        $this->resetPage();
    }

    public function mount(ClinicContext $clinicContext): void
    {
        $this->clinicContext = $clinicContext;

        $user = auth()->user();
        $this->isAdmin = $user->isAdmin();

        $clinicContext->initialize($user);

        $this->clinicOptions = $clinicContext->availableClinics($user)
            ->map(fn ($clinic) => [
                'id' => $clinic->id,
                'name' => $clinic->name,
            ])
            ->toArray();

        $this->viewingAllClinics = $clinicContext->isAllClinicsSelection($user);
        $currentClinicId = $clinicContext->currentClinicId($user);

        if ($this->isAdmin) {
            $this->clinicId = $this->viewingAllClinics ? 'all' : ($currentClinicId ? (string) $currentClinicId : null);
        } else {
            $this->clinicId = $currentClinicId ? (string) $currentClinicId : null;
        }

        $this->invoiceDate = today()->format('Y-m-d');

        $this->loadSelectableData();
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

        $this->resetForm();
        $this->loadSelectableData();
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openModal(): void
    {
        if ($this->isAdmin && $this->viewingAllClinics) {
            session()->flash('error', 'Select a clinic before creating an invoice.');
            return;
        }

        if (!$this->getActiveClinicId()) {
            session()->flash('error', 'Assign a clinic before creating an invoice.');
            return;
        }

        $this->resetForm();

        // UX: show the treatment selector immediately so users can see available
        // treatments without having to click "Add Treatment" first.
        $this->addTreatmentLine();

        $this->isModalOpen = true;
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
    }

    public function addTreatmentLine(): void
    {
        $this->treatmentLines[] = [
            'treatment_package_id' => null,
            'price' => 0,
            'qty' => 1,
            'discount' => 0,
            'discount_type' => 'fixed',
            'subtotal' => 0,
        ];
    }

    public function removeTreatmentLine($index): void
    {
        unset($this->treatmentLines[$index]);
        $this->treatmentLines = array_values($this->treatmentLines);
        $this->calculateTotals();
    }

    public function updatedTreatmentLines($value, $key): void
    {
        [$index, $field] = array_pad(explode('.', $key), 2, null);
        $index = (int) $index;

        if ($field === 'treatment_package_id' && $value) {
            $package = TreatmentPackage::find($value);
            if ($package) {
                $this->treatmentLines[$index]['price'] = $package->price ?? 0;
            }
        }

        $this->calculateTotals();
    }

    public function addSaleLine(): void
    {
        $this->saleLines[] = [
            'pharmacy_id' => null,
            'price' => 0,
            'qty' => 1,
            'discount' => 0,
            'discount_type' => 'fixed',
            'subtotal' => 0,
        ];
    }

    public function removeSaleLine($index): void
    {
        unset($this->saleLines[$index]);
        $this->saleLines = array_values($this->saleLines);
        $this->calculateTotals();
    }

    public function updatedSaleLines($value, $key): void
    {
        [$index, $field] = array_pad(explode('.', $key), 2, null);
        $index = (int) $index;

        if ($field === 'pharmacy_id' && $value) {
            $pharmacy = Pharmacy::find($value);
            if ($pharmacy) {
                $this->saleLines[$index]['price'] = $pharmacy->selling_price ?? 0;
            }
        }

        $this->calculateTotals();
    }

    public function viewInvoice(int $invoiceId): void
    {
        $invoice = Invoice::with(['clinic', 'patient', 'items.treatmentPackage', 'items.pharmacy'])
            ->findOrFail($invoiceId);

        $this->viewingInvoice = $invoice;
        $this->isViewingInvoice = true;
    }

    public function closeViewModal(): void
    {
        $this->viewingInvoice = null;
        $this->isViewingInvoice = false;
    }

    public function saveInvoice(): void
    {
        $this->calculateTotals();

        $clinicId = $this->getActiveClinicId();

        if (!$clinicId) {
            session()->flash('error', 'Select a clinic before saving the invoice.');
            return;
        }

        $lineItems = $this->prepareLineItems();

        if (empty($lineItems)) {
            $this->addError('lineItems', 'Add at least one line item before saving.');
            return;
        }

        if ($this->requiresPatient($lineItems) && !$this->patientId) {
            $this->addError('patientId', 'Select a patient when adding treatment items.');
            return;
        }

        $this->validate([
            'invoiceDate' => 'required|date',
            'dueDate' => 'nullable|date|after_or_equal:invoiceDate',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'patientId' => 'nullable|exists:patients,id',
            'notes' => 'nullable|string',
        ]);

        $clinic = Clinic::findOrFail($clinicId);

        DB::transaction(function () use ($clinic, $lineItems) {
            $invoiceNumber = $this->generateInvoiceNumber($clinic);

            $invoice = Invoice::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $this->patientId,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => Carbon::createFromFormat('Y-m-d', $this->invoiceDate),
                'due_date' => $this->dueDate ? Carbon::createFromFormat('Y-m-d', $this->dueDate) : null,
                'subtotal' => $this->subtotal,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $this->totalAmount,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);

            foreach ($lineItems as $item) {
                $invoice->items()->create(array_merge($item, [
                    'clinic_id' => $clinic->id,
                ]));
            }

            $this->invoiceNumber = $invoice->invoice_number;

            $this->syncSaleInventory(collect($lineItems)
                ->where('item_type', InvoiceItem::TYPE_SALE)
                ->pluck('pharmacy_id')
                ->filter()
                ->unique()
                ->all());
        });

        session()->flash('success', sprintf('Invoice %s created successfully.', $this->invoiceNumber));

        $this->closeModal();
        $this->resetForm();
        $this->resetPage();
    }

    public function deleteInvoice(int $invoiceId): void
    {
        $invoice = Invoice::with('items')->findOrFail($invoiceId);

        if (!$this->isAdmin) {
            abort(403);
        }

        $pharmacyIds = $invoice->items
            ->where('item_type', InvoiceItem::TYPE_SALE)
            ->pluck('pharmacy_id')
            ->filter()
            ->unique()
            ->all();

        DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->delete();
        });

        $this->syncSaleInventory($pharmacyIds);

        session()->flash('success', 'Invoice deleted successfully.');
        $this->resetPage();
    }

    public function render()
    {
        $invoices = $this->buildQuery()->paginate(10);

        return view('livewire.invoices-manager', [
            'invoices' => $invoices,
        ]);
    }

    protected function buildQuery()
    {
        $query = Invoice::with(['clinic', 'patient', 'items'])
            ->orderByDesc('invoice_date')
            ->orderByDesc('created_at');

        $activeClinicId = $this->getActiveClinicId();

        if ($activeClinicId) {
            $query->forClinic($activeClinicId);
        }

        if ($this->typeFilter !== 'all') {
            if ($this->typeFilter === 'mixed') {
                $query->whereHas('items', function ($itemQuery) {
                    $itemQuery->where('item_type', InvoiceItem::TYPE_TREATMENT);
                })->whereHas('items', function ($itemQuery) {
                    $itemQuery->where('item_type', InvoiceItem::TYPE_SALE);
                });
            } else {
                $query->whereHas('items', function ($itemQuery) {
                    $itemQuery->where('item_type', $this->typeFilter);
                });
            }
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query;
    }

    protected function loadSelectableData(): void
    {
        $clinicId = $this->getActiveClinicId();

        if (!$clinicId) {
            $this->patients = [];
            $this->treatments = [];
            $this->pharmacies = [];
            return;
        }

        $this->patients = Patient::where('clinic_id', $clinicId)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'name' => trim($patient->first_name . ' ' . $patient->last_name),
                ];
            })
            ->toArray();

        // Invoice creation uses global Treatment Packages.
        // (Patients and pharmacy items remain clinic-scoped.)
        $treatmentsQuery = TreatmentPackage::query();

        $this->treatments = $treatmentsQuery
            ->orderBy('name')
            ->get(['id', 'name', 'price'])
            ->map(function ($package) {
                $name = $package->name;

                return [
                    'id' => $package->id,
                    'name' => $name,
                    'price' => $package->price,
                ];
            })
            ->toArray();

        $this->pharmacies = Pharmacy::where('clinic_id', $clinicId)
            ->orderBy('name')
            ->get(['id', 'name', 'selling_price'])
            ->map(function ($pharmacy) {
                return [
                    'id' => $pharmacy->id,
                    'name' => $pharmacy->name,
                    'price' => $pharmacy->selling_price,
                ];
            })
            ->toArray();
    }

    protected function calculateTotals(): void
    {
        $subtotal = 0;

        foreach ($this->treatmentLines as $index => $line) {
            $result = $this->calculateLineSubtotal($line);
            $this->treatmentLines[$index] = $result;
            $subtotal += $result['subtotal'];
        }

        foreach ($this->saleLines as $index => $line) {
            $result = $this->calculateLineSubtotal($line, true);
            $this->saleLines[$index] = $result;
            $subtotal += $result['subtotal'];
        }

        $this->subtotal = round($subtotal, 2);
        $this->totalAmount = $this->subtotal;
    }

    protected function calculateLineSubtotal(array $line, bool $forceQuantity = false): array
    {
        $qty = (int) ($line['qty'] ?? 1);
        if ($qty < 1) {
            $qty = $forceQuantity ? 1 : max($qty, 1);
        }

        $price = (float) ($line['price'] ?? 0);
        if ($price < 0) {
            $price = 0;
        }

        $discount = (float) ($line['discount'] ?? 0);
        if ($discount < 0) {
            $discount = 0;
        }

        $discountType = $line['discount_type'] ?? 'fixed';

        $lineSubtotal = $price * $qty;

        if ($discountType === 'percentage') {
            $discountValue = $lineSubtotal * min($discount, 100) / 100;
        } else {
            $discountValue = min($discount, $lineSubtotal);
        }

        $lineSubtotal = max($lineSubtotal - $discountValue, 0);

        $line['qty'] = $qty;
        $line['price'] = round($price, 2);
        $line['discount'] = round($discount, 2);
        $line['discount_type'] = $discountType === 'percentage' ? 'percentage' : 'fixed';
        $line['subtotal'] = round($lineSubtotal, 2);

        return $line;
    }

    protected function prepareLineItems(): array
    {
        $items = [];

        foreach ($this->treatmentLines as $line) {
            $packageId = $line['treatment_package_id'] ?? null;
            if (!$packageId) {
                continue;
            }

            $items[] = [
                'item_type' => InvoiceItem::TYPE_TREATMENT,
                'treatment_id' => null,
                'treatment_package_id' => (int) $packageId,
                'pharmacy_id' => null,
                'qty' => (int) ($line['qty'] ?? 1),
                'unit_price' => (float) ($line['price'] ?? 0),
                'discount_type' => $line['discount_type'] ?? 'fixed',
                'discount_amount' => (float) ($line['discount'] ?? 0),
                'subtotal' => (float) ($line['subtotal'] ?? 0),
            ];
        }

        foreach ($this->saleLines as $line) {
            $pharmacyId = $line['pharmacy_id'] ?? null;
            if (!$pharmacyId) {
                continue;
            }

            $items[] = [
                'item_type' => InvoiceItem::TYPE_SALE,
                'treatment_id' => null,
                'treatment_package_id' => null,
                'pharmacy_id' => (int) $pharmacyId,
                'qty' => (int) ($line['qty'] ?? 1),
                'unit_price' => (float) ($line['price'] ?? 0),
                'discount_type' => $line['discount_type'] ?? 'fixed',
                'discount_amount' => (float) ($line['discount'] ?? 0),
                'subtotal' => (float) ($line['subtotal'] ?? 0),
            ];
        }

        return $items;
    }

    protected function requiresPatient(array $items): bool
    {
        return collect($items)->contains(fn ($item) => $item['item_type'] === InvoiceItem::TYPE_TREATMENT);
    }

    protected function syncSaleInventory(array $pharmacyIds): void
    {
        foreach ($pharmacyIds as $pharmacyId) {
            $pharmacy = Pharmacy::find($pharmacyId);

            if (!$pharmacy) {
                continue;
            }

            $totalSaleQty = InvoiceItem::where('item_type', InvoiceItem::TYPE_SALE)
                ->where('pharmacy_id', $pharmacy->id)
                ->sum('qty');

            $outOfStock = OutOfStock::firstOrNew(['phar_id' => $pharmacy->id]);
            $outOfStock->clinic_id = $pharmacy->clinic_id;
            $outOfStock->total = $outOfStock->total ?? 0;
            $outOfStock->sale = $totalSaleQty;
            $outOfStock->save();
        }
    }

    protected function generateInvoiceNumber(Clinic $clinic): string
    {
        $nextNumber = $clinic->getNextCounterNumber('invoice');

        return $clinic->prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    protected function resetForm(): void
    {
        $this->invoiceNumber = null;
        $this->invoiceDate = today()->format('Y-m-d');
        $this->dueDate = null;
        $this->status = 'sent';
        $this->notes = null;
        $this->patientId = null;
        $this->treatmentLines = [];
        $this->saleLines = [];
        $this->subtotal = 0;
        $this->totalAmount = 0;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    protected function getActiveClinicId(): ?int
    {
        if ($this->isAdmin && $this->viewingAllClinics) {
            return null;
        }

        if (!$this->clinicId || $this->clinicId === 'all') {
            return null;
        }

        return (int) $this->clinicId;
    }

    protected function resolveClinicContext(): ClinicContext
    {
        if (!$this->clinicContext) {
            $this->clinicContext = app(ClinicContext::class);
        }

        return $this->clinicContext;
    }
}
