<div>
    {{-- Page header, date picker, and "Add Appointment" button --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h3 class="card-title mb-1">Appointments for {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</h3>
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
        <div class="d-flex flex-wrap align-items-center">
            <input type="date" wire:model="date" wire:change="changeDate($event.target.value)" class="form-control mr-2" style="width: 200px;">
            <button wire:click="openModal()" class="btn btn-primary">Add Appointment</button>
        </div>
    </div>

    @if($isAdmin && $viewingAllClinics)
        <div class="alert alert-info">
            Select a clinic to create or edit appointments. Viewing all clinics disables form actions.
        </div>
    @endif

    {{-- Display success messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- Appointments Grid --}}
    <div class="row">
        @foreach($allTimeSlots as $timeSlot)
            <div class="col-md-3 mb-4">
                <div class="card {{ in_array($timeSlot->id, $bookedTimes) ? 'border-primary' : '' }}">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ $timeSlot->custom_time }}</h5>
                    </div>
                    <div class="card-body">
                        @forelse($this->getAppointmentsForTime($timeSlot) as $appointment)
                            <div class="appointment-item mb-2 p-2 border rounded {{ $appointment->status ? 'bg-light' : '' }}">
                                @if($isAdmin && $viewingAllClinics)
                                    <span class="badge badge-info mb-1">{{ optional($appointment->clinic)->name }}</span>
                                @endif
                                <p class="mb-0"><strong>{{ $appointment->name }}</strong></p>
                                <p class="mb-1">{{ $appointment->phone }}</p>
                                <small>{{ $appointment->description }}</small>
                                <div class="mt-2">
                                    <button wire:click="edit({{ $appointment->id }})" class="btn btn-sm btn-info" @if($isAdmin && $viewingAllClinics) disabled @endif>Edit</button>
                                    <button wire:click="delete({{ $appointment->id }})" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger" @if($isAdmin && $viewingAllClinics) disabled @endif>Delete</button>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">Available</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Create/Edit Modal --}}
    @if($isModalOpen)
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog modal-lg" style="max-height: calc(100vh - 3.5rem);">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $appointmentId ? 'Edit Appointment' : 'Create Appointment' }}</h5>
                    <button type="button" class="close" wire:click="closeModal()">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="overflow-y:auto; max-height: calc(100vh - 12rem);">
                    <form wire:submit.prevent="saveAppointment">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" class="form-control" wire:model.defer="name">
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" class="form-control" wire:model.defer="phone">
                            @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        @if($isAdmin)
                        <div class="form-group">
                            <label for="clinic-select">Clinic</label>
                            <select id="clinic-select" class="form-control" wire:model="formClinicId" @if($viewingAllClinics) required @endif>
                                <option value="">Select clinic</option>
                                @foreach($clinicOptions as $clinicOption)
                                    <option value="{{ $clinicOption['id'] }}">{{ $clinicOption['name'] }}</option>
                                @endforeach
                            </select>
                            @error('formClinicId') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        @endif
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" class="form-control" wire:model.defer="description"></textarea>
                            @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="date-modal">Date</label>
                            <input type="date" id="date-modal" class="form-control" wire:model="date">
                            @error('date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Time Slots</label>
                            <div class="row">
                                @foreach($allTimeSlots as $timeSlot)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="{{ $timeSlot->id }}" id="time_{{ $timeSlot->id }}" wire:model="time">
                                        <label class="form-check-label" for="time_{{ $timeSlot->id }}">
                                            {{ $timeSlot->custom_time }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                             @error('time') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" class="form-control" wire:model.defer="status">
                                <option value="0">Pending</option>
                                <option value="1">Finished</option>
                            </select>
                            @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click.prevent="saveAppointment()">Save</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>