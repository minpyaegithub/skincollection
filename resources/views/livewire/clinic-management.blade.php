<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Clinic Management</h3>
                        <div class="card-tools">
                            <button wire:click="showCreateModal" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Clinic
                            </button>
                        </div>
                    </div>
                    <div class="card-body">

                        <!-- Flash Messages -->
                        @if (session()->has('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- Clinics Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Prefix</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($clinics as $clinic)
                                        <tr>
                                            <td>{{ $clinic->id }}</td>
                                            <td>{{ $clinic->name }}</td>
                                            <td>{{ $clinic->prefix }}</td>
                                            <td>{{ $clinic->email }}</td>
                                            <td>{{ $clinic->phone }}</td>
                                            <td>
                                                <span class="badge {{ $clinic->status ? 'badge-success' : 'badge-danger' }}">
                                                    {{ $clinic->status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button wire:click="showEditModal({{ $clinic->id }})" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button wire:click="deleteClinic({{ $clinic->id }})" 
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No clinics found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clinic Modal -->
    @if($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editingClinic ? 'Edit Clinic' : 'Add New Clinic' }}
                        </h5>
                        <button type="button" wire:click="closeModal" class="close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveClinic">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Clinic Name *</label>
                                        <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Prefix *</label>
                                        <input type="text" wire:model="prefix" class="form-control @error('prefix') is-invalid @enderror" maxlength="10">
                                        <small class="text-muted">Short code for the clinic (max 10 characters)</small>
                                        @error('prefix') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" wire:model="email" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" wire:model="phone" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Address</label>
                                <textarea wire:model="address" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select wire:model="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancel</button>
                        <button type="button" wire:click="saveClinic" class="btn btn-primary">
                            {{ $editingClinic ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
