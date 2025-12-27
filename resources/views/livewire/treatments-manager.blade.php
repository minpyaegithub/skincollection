<div class="lw-root">
    @php($formClinic = collect($clinicOptions)->firstWhere('id', (int) $formClinicId))
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="card-title mb-1">Treatments</h3>
            @php($clinicCollection = collect($clinicOptions))
            @php($selectedClinic = $clinicCollection->firstWhere('id', (int) $clinicId))
            @php($defaultClinic = $clinicCollection->first())
            <span class="badge badge-secondary">
                {{ $viewingAllClinics ? 'All Clinics' : data_get($selectedClinic, 'name', data_get($defaultClinic, 'name', 'Clinic')) }}
            </span>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('treatment.export') }}" class="btn btn-outline-success mr-2">
                <i class="fas fa-file-excel"></i> Export
            </a>
            <button class="btn btn-primary" wire:click="openModal">
                <i class="fas fa-plus"></i> New Treatment
            </button>
        </div>
    </div>

    @if($isAdmin && $viewingAllClinics)
        <div class="alert alert-info">Select a clinic before creating or editing treatments.</div>
    @endif

    <!-- <div class="card mb-3">
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-4 mb-0">
                    <label>Status</label>
                    <select class="form-control" wire:model="statusFilter">
                        <option value="all">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group col-md-4 mb-0">
                    <label>Search</label>
                    <input type="text" class="form-control" placeholder="Search by name or description" wire:model.debounce.300ms="search">
                </div>
            </div>
        </div>
    </div> -->

    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered" id="tbl_treatments" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Duration (mins)</th>
                    <th>Status</th>
                    <th>Created</th>
                    @if($isAdmin && $viewingAllClinics)
                        <th>Clinic</th>
                    @endif
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($treatments as $treatment)
                    <tr wire:key="treatment-{{ $treatment->id }}">
                        <td>{{ $treatment->name }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($treatment->description, 60) ?: '—' }}</td>
                        <td>{{ number_format($treatment->price, 2) }}</td>
                        <td>{{ $treatment->duration_minutes ?? '—' }}</td>
                        <td>
                            <span class="badge badge-pill {{ $treatment->is_active ? 'badge-success' : 'badge-secondary' }}">
                                {{ $treatment->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ optional($treatment->created_at)->format('d-m-Y') ?? '—' }}</td>
                        @if($isAdmin && $viewingAllClinics)
                            <td>{{ optional($treatment->clinic)->name ?? '—' }}</td>
                        @endif
                        <td>
                            <button class="btn btn-sm btn-info" wire:click="openModal({{ $treatment->id }})">Edit</button>
                            <button class="btn btn-sm btn-warning" wire:click="toggleStatus({{ $treatment->id }})">
                                {{ $treatment->is_active ? 'Disable' : 'Enable' }}
                            </button>
                            @if($isAdmin)
                                <button class="btn btn-sm btn-danger" onclick="if(confirm('Delete this treatment?')) @this.delete({{ $treatment->id }});">Delete</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($isAdmin && $viewingAllClinics) ? 8 : 7 }}" class="text-center text-muted">No treatments found for this clinic.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- When using DataTables (client-side pagination/search), hide Livewire pagination links. --}}

    @section('scripts')
        <script>
            $(document).ready(function(){
                if (!$.fn || !$.fn.DataTable) return;
                if ($.fn.DataTable.isDataTable('#tbl_treatments')) return;

                $('#tbl_treatments').DataTable({
                    "lengthChange": true,
                    "info": true,
                    "searching": true,
                    "aaSorting": []
                });
            });
        </script>
    @endsection

    @if($isModalOpen)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $treatmentId ? 'Edit Treatment' : 'Create Treatment' }}</h5>
                        <button type="button" class="close" wire:click="closeModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @if($isAdmin)
                            <div class="alert alert-secondary">
                                Managing treatment for <strong>{{ data_get($formClinic, 'name', data_get($selectedClinic, 'name', 'selected clinic')) }}</strong>.
                            </div>
                        @endif
                        <form wire:submit.prevent="saveTreatment">
                            <div class="form-row">
                                @if($isAdmin)
                                    <div class="form-group col-md-6">
                                        <label>Clinic</label>
                                        <select class="form-control" wire:model="formClinicId">
                                            <option value="">Select clinic</option>
                                            @foreach($clinicOptions as $clinicOption)
                                                <option value="{{ $clinicOption['id'] }}">{{ $clinicOption['name'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('formClinicId') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                                <div class="form-group col-md-6">
                                    <label>Name</label>
                                    <input type="text" class="form-control" wire:model.defer="name">
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Price</label>
                                    <input type="number" step="0.01" min="0" class="form-control" wire:model.defer="price">
                                    @error('price') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Duration (minutes)</label>
                                    <input type="number" min="0" class="form-control" wire:model.defer="durationMinutes">
                                    @error('durationMinutes') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" rows="3" wire:model.defer="description"></textarea>
                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="isActive" wire:model.defer="isActive">
                                    <label class="form-check-label" for="isActive">Active</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveTreatment">{{ $treatmentId ? 'Update' : 'Create' }}</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
