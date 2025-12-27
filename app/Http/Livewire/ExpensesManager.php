<?php

namespace App\Http\Livewire;

use App\Models\Expense;
use App\Services\ClinicContext;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ExpensesManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $clinicId;
    public $clinicOptions = [];
    public $viewingAllClinics = false;
    public $isAdmin = false;

    public $expenseId;
    public $category;
    public $amount;
    public $description;
    public $expense_date;
    public $formClinicId;

    public $isModalOpen = false;

    protected ?ClinicContext $clinicContext = null;

    protected $listeners = [
        'confirmExpenseDeletion' => 'delete',
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
        $this->clinicId = $this->viewingAllClinics ? 'all' : ($currentClinicId ? (string) $currentClinicId : null);
        $this->formClinicId = $this->viewingAllClinics ? null : $currentClinicId;
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

        $this->expense_date = today()->format('Y-m-d');
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

        $this->resetPage();
    }

    public function openModal(?int $expenseId = null)
    {
        if ($this->isAdmin && $this->viewingAllClinics && !$expenseId) {
            session()->flash('error', 'Select a clinic before creating an expense.');
            return;
        }

        $this->resetValidation();

        if ($expenseId) {
            $this->loadExpense($expenseId);
        } else {
            $this->resetForm();
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function saveExpense()
    {
        $validated = $this->validate($this->rules());

        $clinicId = $this->isAdmin ? (int) $validated['formClinicId'] : $this->getActiveClinicId();

        if (!$clinicId) {
            session()->flash('error', 'Clinic selection is required.');
            return;
        }

        $isNewExpense = empty($this->expenseId);

        DB::transaction(function () use ($clinicId) {
            $expense = Expense::updateOrCreate(
                ['id' => $this->expenseId],
                [
                    'category' => $this->category,
                    'amount' => $this->amount,
                    'description' => $this->description,
                    'expense_date' => $this->expense_date,
                    'clinic_id' => $clinicId,
                ]
            );

            $this->expenseId = $expense->id;
        });

        session()->flash('success', $isNewExpense ? 'Expense Created Successfully.' : 'Expense Updated Successfully.');

        $this->closeModal();
        $this->resetPage();
    }

    public function delete(int $expenseId)
    {
        $expense = Expense::findOrFail($expenseId);
        $this->ensureExpenseAccessible($expense);

        $expense->delete();

        session()->flash('success', 'Expense Deleted Successfully.');

        $this->resetPage();
    }

    public function render()
    {
        $expenses = $this->buildQuery()->paginate(10);

        return view('livewire.expenses-manager', [
            'expenses' => $expenses,
        ]);
    }

    protected function buildQuery()
    {
        $query = Expense::with('clinic')->orderByDesc('expense_date')->orderByDesc('created_at');

        $activeClinicId = $this->getActiveClinicId();

        if ($activeClinicId) {
            $query->forClinic($activeClinicId);
        }

        return $query;
    }

    protected function loadExpense(int $expenseId): void
    {
        $expense = Expense::findOrFail($expenseId);
        $this->ensureExpenseAccessible($expense);

        $this->expenseId = $expense->id;
        $this->category = $expense->category;
        $this->amount = $expense->amount;
        $this->description = $expense->description;
        $this->expense_date = optional($expense->expense_date)->format('Y-m-d') ?? today()->format('Y-m-d');
        $this->formClinicId = $expense->clinic_id;
    }

    protected function resetForm(): void
    {
        $this->expenseId = null;
        $this->category = '';
        $this->amount = '';
        $this->description = '';
        $this->expense_date = today()->format('Y-m-d');
        $this->formClinicId = $this->isAdmin && $this->viewingAllClinics ? null : $this->getActiveClinicId();
    }

    protected function rules(): array
    {
        $rules = [
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'expense_date' => 'required|date',
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

    protected function ensureExpenseAccessible(Expense $expense): void
    {
        if ($this->isAdmin) {
            return;
        }

        $activeClinicId = $this->getActiveClinicId();

        if (!$activeClinicId || $expense->clinic_id !== $activeClinicId) {
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
