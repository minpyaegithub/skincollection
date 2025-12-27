<div>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="card-title mb-1">Expenses</h3>
            @php($clinicCollection = collect($clinicOptions))
            @php($selectedClinic = $clinicCollection->firstWhere('id', (int) $clinicId))
            @php($defaultClinic = $clinicCollection->first())
            @if($isAdmin)
                <span class="badge badge-secondary">
                    {{ $viewingAllClinics ? 'All Clinics' : data_get($selectedClinic, 'name', 'Select Clinic') }}
                </span>
            @else
                <span class="badge badge-secondary">{{ data_get($defaultClinic, 'name', 'Clinic') }}</span>
            @endif
        </div>
        <div class="d-flex align-items-center">
            
            <button class="btn btn-primary" wire:click="openModal()">
                <i class="fas fa-plus"></i> Add Expense
            </button>
        </div>
    </div>

    @if($isAdmin && $viewingAllClinics)
        <div class="alert alert-info">Select a clinic before creating or editing expenses.</div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    @if($isAdmin && $viewingAllClinics)
                        <th>Clinic</th>
                    @endif
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Expense Date</th>
                    <th>Recorded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr wire:key="expense-{{ $expense->id }}">
                        @if($isAdmin && $viewingAllClinics)
                            <td>{{ optional($expense->clinic)->name ?? '—' }}</td>
                        @endif
                        <td>{{ $expense->category }}</td>
                        <td>{{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->description }}</td>
                        <td>{{ optional($expense->expense_date)->format('d-m-Y') ?? '—' }}</td>
                        <td>{{ optional($expense->created_at)->format('d-m-Y') ?? '—' }}</td>
                        <td>
                            <button class="btn btn-sm btn-info" wire:click="openModal({{ $expense->id }})">Edit</button>
                            <button class="btn btn-sm btn-danger" wire:click="delete({{ $expense->id }})" onclick="return confirm('Delete this expense?')">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin && $viewingAllClinics ? 7 : 6 }}" class="text-center text-muted">No expenses recorded for this clinic.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $expenses->links() }}
    </div>

    @if($isModalOpen)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $expenseId ? 'Edit Expense' : 'Add Expense' }}</h5>
                        <button type="button" class="close" wire:click="closeModal()"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveExpense">
                            @if($isAdmin)
                                <div class="form-group">
                                    <label for="formClinic">Clinic</label>
                                    <select id="formClinic" class="form-control" wire:model="formClinicId">
                                        <option value="">-- Select Clinic --</option>
                                        @foreach($clinicOptions as $clinicOption)
                                            <option value="{{ $clinicOption['id'] }}">{{ $clinicOption['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('formClinicId') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="category">Category</label>
                                <input type="text" id="category" class="form-control" wire:model.defer="category">
                                @error('category') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="amount">Amount</label>
                                <input type="number" step="0.01" id="amount" class="form-control" wire:model.defer="amount">
                                @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="expense_date">Expense Date</label>
                                <input type="date" id="expense_date" class="form-control" wire:model.defer="expense_date">
                                @error('expense_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" class="form-control" rows="3" wire:model.defer="description"></textarea>
                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal()">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveExpense">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
