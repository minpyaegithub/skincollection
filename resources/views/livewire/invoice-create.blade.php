<div>
    <form wire:submit.prevent="saveInvoice">
        {{-- Header --}}
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Invoice Number</label>
                    <input type="text" class="form-control" wire:model.defer="invoice_no" readonly>
                    @error('invoice_no') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Invoice Date</label>
                    <input type="date" class="form-control" wire:model.defer="invoice_date">
                    @error('invoice_date') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Invoice Type</label>
                    <select class="form-control" wire:model="type">
                        <option value="treatment">Treatment</option>
                        <option value="sale">Sale</option>
                    </select>
                </div>
            </div>
        </div>

        @if ($type === 'treatment')
            <div class="form-group">
                <label>Patient</label>
                <select class="form-control" wire:model.defer="patient_id">
                    <option value="">Select Patient</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}">{{ $patient->getPatientFullName() }} ({{ $patient->token }})</option>
                    @endforeach
                </select>
                @error('patient_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        @endif

        <hr>

        {{-- Dynamic Lines --}}
        @if ($type === 'treatment')
            <h4>Treatments</h4>
            @error('treatmentLines') <div class="alert alert-danger">{{ $message }}</div> @enderror
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Treatment</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Sub Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($treatmentLines as $index => $line)
                        <tr>
                            <td>
                                <select class="form-control" wire:model="treatmentLines.{{ $index }}.select_treatment">
                                    <option value="">Select Treatment</option>
                                    @foreach ($treatments as $treatment)
                                        <option value="{{ $treatment->id }}">{{ $treatment->name }}</option>
                                    @endforeach
                                </select>
                                @error('treatmentLines.'.$index.'.select_treatment') <span class="text-danger">{{ $message }}</span> @enderror
                            </td>
                            <td><input type="number" class="form-control" wire:model.lazy="treatmentLines.{{ $index }}.price" readonly></td>
                            <td>
                                <div class="input-group">
                                    <input type="number" class="form-control" wire:model.lazy="treatmentLines.{{ $index }}.discount">
                                    <div class="input-group-append">
                                        <select class="form-control" wire:model.lazy="treatmentLines.{{ $index }}.discount_type">
                                            <option value="fixed">Fixed</option>
                                            <option value="percentage">%</option>
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td><input type="number" class="form-control" wire:model="treatmentLines.{{ $index }}.sub_total" readonly></td>
                            <td>
                                <button type="button" class="btn btn-danger" wire:click="removeTreatmentLine({{ $index }})">Remove</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="button" class="btn btn-primary" wire:click="addTreatmentLine">Add Treatment</button>
        @endif

        @if ($type === 'sale')
            <h4>Sale Items</h4>
            @error('saleLines') <div class="alert alert-danger">{{ $message }}</div> @enderror
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Discount</th>
                        <th>Sub Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($saleLines as $index => $line)
                        <tr>
                            <td>
                                <select class="form-control" wire:model="saleLines.{{ $index }}.select_pharmacy">
                                    <option value="">Select Product</option>
                                    @foreach ($pharmacies as $pharmacy)
                                        <option value="{{ $pharmacy->id }}">{{ $pharmacy->name }}</option>
                                    @endforeach
                                </select>
                                @error('saleLines.'.$index.'.select_pharmacy') <span class="text-danger">{{ $message }}</span> @enderror
                            </td>
                            <td><input type="number" class="form-control" wire:model.lazy="saleLines.{{ $index }}.qty"></td>
                            <td><input type="number" class="form-control" wire:model.lazy="saleLines.{{ $index }}.price"></td>
                            <td>
                                <div class="input-group">
                                    <input type="number" class="form-control" wire:model.lazy="saleLines.{{ $index }}.discount">
                                    <div class="input-group-append">
                                        <select class="form-control" wire:model.lazy="saleLines.{{ $index }}.discount_type">
                                            <option value="fixed">Fixed</option>
                                            <option value="percentage">%</option>
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td><input type="number" class="form-control" wire:model="saleLines.{{ $index }}.sub_total" readonly></td>
                            <td>
                                <button type="button" class="btn btn-danger" wire:click="removeSaleLine({{ $index }})">Remove</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="button" class="btn btn-primary" wire:click="addSaleLine">Add Product</button>
        @endif

        <hr>

        {{-- Totals and Save --}}
        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label"><strong>Grand Total</strong></label>
                    <div class="col-sm-8">
                        <input type="text" readonly class="form-control-plaintext" wire:model="grand_total">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-success btn-user float-right mb-3">Save Invoice</button>
            <a class="btn btn-primary float-right mr-3 mb-3" href="{{ route('invoices.index') }}">Cancel</a>
        </div>
    </form>
</div>
