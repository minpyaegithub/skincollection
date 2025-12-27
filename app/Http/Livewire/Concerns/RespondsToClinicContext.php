<?php

namespace App\Http\Livewire\Concerns;

/**
 * Add consistent handling for the global clinic context change event.
 *
 * Livewire v3 can receive global `dispatch('clinicContextChanged', ...)` events.
 * This trait also works when a component is targeted via `emitTo(..., 'clinicContextChanged', ...)`.
 */
trait RespondsToClinicContext
{
    /**
     * Livewire listener map (v2/v3 compatible).
     *
     * Note: If your component already defines $listeners, merge this in manually.
     */
    protected array $clinicContextListeners = [
        'clinicContextChanged' => 'handleClinicContextChanged',
    ];

    /**
     * Livewire v3 direct event handler when dispatching `clinicContextChanged`.
     */
    public function clinicContextChanged($payload = null): void
    {
        if (method_exists($this, 'handleClinicContextChanged')) {
            $this->handleClinicContextChanged($payload);
        }
    }

    /**
     * Helper to apply the clinic payload onto common public props.
     */
    protected function applyClinicPayload($payload): bool
    {
        if (!is_array($payload) || !array_key_exists('clinicId', $payload)) {
            return false;
        }

        $clinicId = $payload['clinicId'];

        if (property_exists($this, 'viewingAllClinics') && array_key_exists('viewingAllClinics', $payload)) {
            $this->viewingAllClinics = (bool) $payload['viewingAllClinics'];
        }

        if (property_exists($this, 'clinicId')) {
            $this->clinicId = $clinicId === null ? 'all' : (string) $clinicId;
        }

        if (property_exists($this, 'formClinicId')) {
            $this->formClinicId = $clinicId === null ? null : (int) $clinicId;
        }

        return true;
    }
}
