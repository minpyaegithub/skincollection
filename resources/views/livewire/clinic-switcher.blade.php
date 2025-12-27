<div>
    <select id="clinic_switcher" name="clinic_switcher" class="form-control" wire:model="clinicId" wire:change="switchClinic($event.target.value)">
        <option value="all">All Clinics</option>
        @foreach($clinicOptions as $clinicOption)
            <option value="{{ $clinicOption['id'] }}">
                {{ $clinicOption['name'] }}
            </option>
        @endforeach
    </select>
</div>
