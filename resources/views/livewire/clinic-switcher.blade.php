<div>
    <select id="clinic_switcher" name="clinic_switcher" class="form-control" wire:change="switchClinic($event.target.value)">
        <option value="all" @selected($clinicId === 'all')>All Clinics</option>
        @foreach($clinicOptions as $clinicOption)
            <option value="{{ $clinicOption['id'] }}" @selected((string)$clinicId === (string)$clinicOption['id'])>
                {{ $clinicOption['name'] }}
            </option>
        @endforeach
    </select>
</div>
