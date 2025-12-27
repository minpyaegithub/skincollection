<div>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="card-title mb-1">Invoices</h3>
            @php($clinicCollection = collect($clinicOptions))
            @php($selectedClinic = $clinicCollection->firstWhere('id', (int) $clinicId))
            @php($defaultClinic = $clinicCollection->first())
            <span class="badge badge-secondary">
                {{ $viewingAllClinics ? 'All Clinics' : data_get($selectedClinic, 'name', data_get($defaultClinic, 'name', 'Clinic')) }}
            </span>
        </div>
        <div class="d-flex align-items-center">
            
            <button class="btn btn-primary" wire:click="openModal">
                <i class="fas fa-plus"></i> New Invoice
            </button>
        </div>
    </div>

    @if($isAdmin && $viewingAllClinics)
        <div class="alert alert-info">Select a clinic before creating or editing invoices.</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-3 mb-0">
                    <label>Type</label>
                    <select class="form-control" wire:model="typeFilter">
                        <option value="all">All</option>
                        <option value="treatment">Treatment</option>
                        <option value="sale">Sale</option>
                        <option value="mixed">Mixed</option>
                    </select>
                </div>
                <div class="form-group col-md-3 mb-0">
                    <label>Status</label>
                    <select class="form-control" wire:model="statusFilter">
                        <option value="all">All</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

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
                    <th>Invoice #</th>
                    @if($isAdmin && $viewingAllClinics)
                        <th>Clinic</th>
                    @endif
                    <th>Patient</th>
                    <th>Invoice Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($invoices->count())
                    @foreach($invoices as $invoice)
                        @php($treatmentCount = $invoice->items->where('item_type', \App\Models\InvoiceItem::TYPE_TREATMENT)->count())
                        @php($saleCount = $invoice->items->where('item_type', \App\Models\InvoiceItem::TYPE_SALE)->count())
                        @php($summaryParts = collect([
                            $treatmentCount ? $treatmentCount.' Treatment'.($treatmentCount > 1 ? 's' : '') : null,
                            $saleCount ? $saleCount.' Product'.($saleCount > 1 ? 's' : '') : null,
                        ])->filter())
                    <tr wire:key="invoice-{{ $invoice->id }}">
                        <td>{{ $invoice->invoice_number }}</td>
                        @if($isAdmin && $viewingAllClinics)
                            <td>{{ optional($invoice->clinic)->name ?? '—' }}</td>
                        @endif
                        <td>{{ optional($invoice->patient)->getPatientFullName() ?? '—' }}</td>
                        <td>{{ optional($invoice->invoice_date)->format('d-m-Y') ?? '—' }}</td>
                        <td>{{ $summaryParts->isNotEmpty() ? $summaryParts->implode(', ') : '—' }}</td>
                        <td>{{ number_format($invoice->total_amount, 2) }}</td>
                        <td>
                            <span class="badge badge-pill {{ $invoice->status === 'paid' ? 'badge-success' : ($invoice->status === 'cancelled' ? 'badge-danger' : 'badge-info') }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" wire:click="viewInvoice({{ $invoice->id }})">View</button>
                            @if($isAdmin)
                                <button class="btn btn-sm btn-danger" onclick="if(confirm('Delete this invoice?')) @this.deleteInvoice({{ $invoice->id }});">Delete</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="{{ ($isAdmin && $viewingAllClinics) ? 8 : 7 }}" class="text-center text-muted">No invoices found for this clinic.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $invoices->links() }}
    </div>

    @if($isModalOpen)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-xl" style="max-height: calc(100vh - 3.5rem); margin-top: 1.75rem; margin-bottom: 1.75rem;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Invoice</h5>
                        <button type="button" class="close" wire:click="closeModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" style="overflow-y: auto; max-height: calc(100vh - 14rem);">
                        @if($isAdmin)
                            <div class="alert alert-secondary">
                                Creating invoice for <strong>{{ data_get($selectedClinic, 'name', 'selected clinic') }}</strong>.
                            </div>
                        @endif
                        <form wire:submit.prevent="saveInvoice">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Invoice Date</label>
                                    <input type="date" class="form-control" wire:model.defer="invoiceDate">
                                    @error('invoiceDate') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Due Date</label>
                                    <input type="date" class="form-control" wire:model.defer="dueDate">
                                    @error('dueDate') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Status</label>
                                    <select class="form-control" wire:model.defer="status">
                                        <option value="draft">Draft</option>
                                        <option value="sent">Sent</option>
                                        <option value="paid">Paid</option>
                                        <option value="overdue">Overdue</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Patient <small class="text-muted">(required when adding treatments)</small></label>
                                <select class="form-control" wire:model.defer="patientId">
                                    <option value="">Select patient</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient['id'] }}">{{ $patient['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('patientId') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label>Notes</label>
                                <textarea class="form-control" wire:model.defer="notes" rows="3"></textarea>
                                @error('notes') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <hr>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h5 class="mb-0">Treatment Items</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addTreatmentLine">Add Treatment</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width:30%">Treatment</th>
                                            <th style="width:10%">Qty</th>
                                            <th style="width:15%">Price</th>
                                            <th style="width:20%">Discount</th>
                                            <th style="width:15%">Subtotal</th>
                                            <th style="width:10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($treatmentLines as $index => $line)
                                            <tr>
                                                <td>
                                                    <select class="form-control" wire:model="treatmentLines.{{ $index }}.treatment_package_id">
                                                        <option value="">Select package</option>
                                                        @foreach($treatments as $option)
                                                            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="number" min="1" class="form-control" wire:model.lazy="treatmentLines.{{ $index }}.qty"></td>
                                                <td><input type="number" step="0.01" min="0" class="form-control" wire:model.lazy="treatmentLines.{{ $index }}.price"></td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" min="0" class="form-control" wire:model.lazy="treatmentLines.{{ $index }}.discount">
                                                        <div class="input-group-append">
                                                            <select class="form-control" wire:model="treatmentLines.{{ $index }}.discount_type">
                                                                <option value="fixed">{{ __('Fixed') }}</option>
                                                                <option value="percentage">{{ __('%') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><input type="number" class="form-control" wire:model="treatmentLines.{{ $index }}.subtotal" readonly></td>
                                                <td><button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeTreatmentLine({{ $index }})">Remove</button></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No treatment items added.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <hr>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h5 class="mb-0">Sale Items</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addSaleLine">Add Product</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width:30%">Product</th>
                                            <th style="width:10%">Qty</th>
                                            <th style="width:15%">Price</th>
                                            <th style="width:20%">Discount</th>
                                            <th style="width:15%">Subtotal</th>
                                            <th style="width:10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($saleLines as $index => $line)
                                            <tr>
                                                <td>
                                                    <select class="form-control" wire:model="saleLines.{{ $index }}.pharmacy_id">
                                                        <option value="">Select product</option>
                                                        @foreach($pharmacies as $option)
                                                            <option value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="number" min="1" class="form-control" wire:model.lazy="saleLines.{{ $index }}.qty"></td>
                                                <td><input type="number" step="0.01" min="0" class="form-control" wire:model.lazy="saleLines.{{ $index }}.price"></td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="number" step="0.01" min="0" class="form-control" wire:model.lazy="saleLines.{{ $index }}.discount">
                                                        <div class="input-group-append">
                                                            <select class="form-control" wire:model="saleLines.{{ $index }}.discount_type">
                                                                <option value="fixed">{{ __('Fixed') }}</option>
                                                                <option value="percentage">{{ __('%') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><input type="number" class="form-control" wire:model="saleLines.{{ $index }}.subtotal" readonly></td>
                                                <td><button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeSaleLine({{ $index }})">Remove</button></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No sale items added.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @error('lineItems')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <div class="d-flex justify-content-end mt-3">
                                <div class="text-right">
                                    <div><strong>Subtotal:</strong> {{ number_format($subtotal, 2) }}</div>
                                    <div><strong>Total:</strong> {{ number_format($totalAmount, 2) }}</div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveInvoice">Save Invoice</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    @if($isViewingInvoice && $viewingInvoice)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Invoice Details</h5>
                        <button type="button" class="close" wire:click="closeViewModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <strong>Invoice #:</strong> {{ $viewingInvoice->invoice_number }}<br>
                            <strong>Clinic:</strong> {{ optional($viewingInvoice->clinic)->name ?? '—' }}<br>
                            <strong>Patient:</strong> {{ optional($viewingInvoice->patient)->getPatientFullName() ?? '—' }}<br>
                            <strong>Date:</strong> {{ optional($viewingInvoice->invoice_date)->format('d-m-Y') ?? '—' }}<br>
                            <strong>Status:</strong> {{ ucfirst($viewingInvoice->status) }}
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($viewingInvoice->items as $item)
                                        <tr>
                                            <td>{{ ucfirst($item->item_type) }}</td>
                                            <td>
                                                @if($item->item_type === \App\Models\InvoiceItem::TYPE_TREATMENT)
                                                    {{ optional($item->treatmentPackage)->name ?? optional($item->treatment)->name ?? '—' }}
                                                @else
                                                    {{ optional($item->pharmacy)->name ?? '—' }}
                                                @endif
                                            </td>
                                            <td>{{ $item->qty }}</td>
                                            <td>{{ number_format($item->unit_price, 2) }}</td>
                                            <td>
                                                {{ $item->discount_type === 'percentage' ? $item->discount_amount.'%' : number_format($item->discount_amount, 2) }}
                                            </td>
                                            <td>{{ number_format($item->subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="text-right">
                            <div><strong>Subtotal:</strong> {{ number_format($viewingInvoice->subtotal, 2) }}</div>
                            <div><strong>Total:</strong> {{ number_format($viewingInvoice->total_amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
