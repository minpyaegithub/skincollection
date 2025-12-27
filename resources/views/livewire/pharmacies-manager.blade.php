<div>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="card-title mb-1">Pharmacy Inventory</h3>
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
                <i class="fas fa-plus"></i> Add Pharmacy
            </button>
        </div>
    </div>

    @if($isAdmin && $viewingAllClinics)
        <div class="alert alert-info">Select a clinic before creating or editing pharmacy items.</div>
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
                    <th>Name</th>
                    <th>Selling Price</th>
                    @if($isAdmin)
                        <th>Purchase Price</th>
                    @endif
                    <th>Available Stock</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pharmacies as $pharmacy)
                    @php($stock = optional($pharmacy->stockSummary))
                    @php($available = max(($stock->total ?? 0) - ($stock->sale ?? 0), 0))
                    <tr wire:key="pharmacy-{{ $pharmacy->id }}">
                        @if($isAdmin && $viewingAllClinics)
                            <td>{{ optional($pharmacy->clinic)->name ?? '—' }}</td>
                        @endif
                        <td>{{ $pharmacy->name }}</td>
                        <td>{{ number_format($pharmacy->selling_price ?? 0, 2) }}</td>
                        @if($isAdmin)
                            <td>{{ number_format($pharmacy->net_price ?? 0, 2) }}</td>
                        @endif
                        <td>{{ $available }}</td>
                        <td>{{ optional($pharmacy->created_at)->format('d-m-Y') ?? '—' }}</td>
                        <td>
                            <button class="btn btn-sm btn-info" wire:click="openModal({{ $pharmacy->id }})">Edit</button>
                            @if($isAdmin)
                                <button class="btn btn-sm btn-danger" wire:click="delete({{ $pharmacy->id }})" onclick="return confirm('Delete this pharmacy item?')">Delete</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($isAdmin && $viewingAllClinics ? 7 : 6) - ($isAdmin ? 0 : 1) }}" class="text-center text-muted">No pharmacy items found for this clinic.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $pharmacies->links() }}
    </div>

    @if($isModalOpen)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $pharmacyId ? 'Edit Pharmacy' : 'Add Pharmacy' }}</h5>
                        <button type="button" class="close" wire:click="closeModal()"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="savePharmacy">
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
                                <label for="name">Name</label>
                                <input type="text" id="name" class="form-control" wire:model.defer="name">
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal()">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="savePharmacy">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
