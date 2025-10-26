<div>
    {{-- Page header, date picker, and "Add Appointment" button --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="card-title">Appointments for {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</h3>
        <div>
            <input type="date" wire:model="date" class="form-control d-inline-block" style="width: 200px;">
            <button wire:click="openModal()" class="btn btn-primary ml-2">Add Appointment</button>
        </div>
    </div>

    {{-- Display success messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
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
                                <p class="mb-0"><strong>{{ $appointment->name }}</strong></p>
                                <p class="mb-1">{{ $appointment->phone }}</p>
                                <small>{{ $appointment->description }}</small>
                                <div class="mt-2">
                                    <button wire:click="edit({{ $appointment->id }})" class="btn btn-sm btn-info">Edit</button>
                                    <button wire:click="delete({{ $appointment->id }})" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</button>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $appointmentId ? 'Edit Appointment' : 'Create Appointment' }}</h5>
                    <button type="button" class="close" wire:click="closeModal()">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
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