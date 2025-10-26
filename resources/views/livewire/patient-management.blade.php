<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Patient Management</h3>
                        <div class="card-tools">
                            <button wire:click="showCreateModal" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Patient
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Search -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" wire:model="search" class="form-control" placeholder="Search patients...">
                            </div>
                        </div>

                        <!-- Flash Messages -->
                        @if (session()->has('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- Patients Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Gender</th>
                                        <th>Age</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($patients as $patient)
                                        <tr>
                                            <td>{{ $patient->id }}</td>
                                            <td>{{ $patient->getPatientFullName() }}</td>
                                            <td>{{ $patient->email }}</td>
                                            <td>{{ $patient->phone }}</td>
                                            <td>{{ ucfirst($patient->gender) }}</td>
                                            <td>{{ $patient->age }}</td>
                                            <td>
                                                <button wire:click="showEditModal({{ $patient->id }})" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button wire:click="deletePatient({{ $patient->id }})" 
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No patients found</td>
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

    <!-- Patient Modal -->
    @if($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editingPatient ? 'Edit Patient' : 'Add New Patient' }}
                        </h5>
                        <button type="button" wire:click="closeModal" class="close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="savePatient">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>First Name *</label>
                                        <input type="text" wire:model="first_name" class="form-control @error('first_name') is-invalid @enderror">
                                        @error('first_name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" wire:model="last_name" class="form-control">
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

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Gender *</label>
                                        <select wire:model="gender" class="form-control">
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Age</label>
                                        <input type="number" wire:model="age" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Weight (kg)</label>
                                        <input type="number" step="0.01" wire:model="weight" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Height (Feet)</label>
                                        <input type="number" wire:model="feet" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Height (Inches)</label>
                                        <input type="number" wire:model="inches" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Address</label>
                                <textarea wire:model="address" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Disease/Condition</label>
                                <textarea wire:model="disease" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Photos</label>
                                <input type="file" wire:model="photos" multiple accept="image/*" class="form-control">
                                <small class="text-muted">You can select multiple images</small>
                            </div>

                            @if($uploadedPhotos)
                                <div class="form-group">
                                    <label>Current Photos</label>
                                    <div class="row">
                                        @foreach($uploadedPhotos as $photo)
                                            <div class="col-md-3">
                                                <img src="{{ \App\Services\S3Service::url($photo) }}" 
                                                     class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancel</button>
                        <button type="button" wire:click="savePatient" class="btn btn-primary">
                            {{ $editingPatient ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
