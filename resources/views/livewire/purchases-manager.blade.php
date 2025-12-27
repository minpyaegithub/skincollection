<div>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="card-title mb-1">Purchases</h3>
            @php($clinicCollection = collect($clinicOptions))
            @php($selectedClinic = $clinicCollection->firstWhere('id', (int) $clinicId))
            @php($defaultClinic = $clinicCollection->first())
            <span class="badge badge-secondary">
                {{ $viewingAllClinics ? 'All Clinics' : data_get($selectedClinic, 'name', data_get($defaultClinic, 'name', 'Clinic')) }}
            </span>
        </div>
        <div class="d-flex align-items-center">
            <div class="mr-2">
                <select class="form-control" wire:model="clinicId">
                    <option value="all">All Clinics</option>
                    @foreach($clinicOptions as $clinicOption)
                        <option value="{{ $clinicOption['id'] }}">{{ $clinicOption['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary" wire:click="openModal()">
                <i class="fas fa-plus"></i> Add Purchase
            </button>
        </div>
    </div>

    @if($viewingAllClinics)
        <div class="alert alert-info">Select a clinic before creating or editing purchases.</div>
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
                    @if($viewingAllClinics)
                        <th>Clinic</th>
                    @endif
                    <th>Pharmacy</th>
                    <th>Quantity</th>
                    <th>Selling Price</th>
                    <th>Purchase Price</th>
                    <th>Purchase Date</th>
                    <th>Recorded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr wire:key="purchase-{{ $purchase->id }}">
                        @if($viewingAllClinics)
                            <td>{{ optional($purchase->clinic)->name ?? '—' }}</td>
                        @endif
                        <td>{{ optional($purchase->pharmacy)->name ?? '—' }}</td>
                        <td>{{ $purchase->qty }}</td>
                        <td>{{ number_format($purchase->selling_price, 2) }}</td>
                        <td>{{ number_format($purchase->net_price, 2) }}</td>
                        <td>{{ optional($purchase->created_time)->format('d-m-Y') ?? '—' }}</td>
                        <td>{{ optional($purchase->created_at)->format('d-m-Y H:i') ?? '—' }}</td>
                        <td>
                            <button class="btn btn-sm btn-info" wire:click="openModal({{ $purchase->id }})">Edit</button>
                            <button class="btn btn-sm btn-danger" wire:click="delete({{ $purchase->id }})" onclick="return confirm('Delete this purchase record?')">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $viewingAllClinics ? 8 : 7 }}" class="text-center text-muted">No purchases recorded for this clinic.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $purchases->links() }}
    </div>

    @if($isModalOpen)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $purchaseId ? 'Edit Purchase' : 'Add Purchase' }}</h5>
                        <button type="button" class="close" wire:click="closeModal()"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="savePurchase">
                            <div class="form-group">
                                <label for="pharmacy">Pharmacy</label>
                                <select id="pharmacy" class="form-control" wire:model="pharmacyId">
                                    <option value="">-- Select Pharmacy --</option>
                                    @foreach($pharmacyOptions as $option)
                                        <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('pharmacyId') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="qty">Quantity</label>
                                <input type="number" min="1" id="qty" class="form-control" wire:model.defer="qty">
                                @error('qty') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="selling_price">Selling Price</label>
                                <input type="number" step="0.01" min="0" id="selling_price" class="form-control" wire:model.defer="selling_price">
                                @error('selling_price') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="net_price">Purchase Price</label>
                                <input type="number" step="0.01" min="0" id="net_price" class="form-control" wire:model.defer="net_price">
                                @error('net_price') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label for="purchase_date">Purchase Date</label>
                                <input type="date" id="purchase_date" class="form-control" wire:model.defer="purchase_date">
                                @error('purchase_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal()">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="savePurchase">Save</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
