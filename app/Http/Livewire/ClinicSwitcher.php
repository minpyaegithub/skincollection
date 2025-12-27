<?php

namespace App\Http\Livewire;

use App\Services\ClinicContext;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClinicSwitcher extends Component
{
    public $clinicId;
    public $clinicOptions = [];
    public $viewingAllClinics = false;
    public $redirectUrl;

    protected $listeners = ['refreshClinicSwitcher' => 'loadContext'];

    public function mount(ClinicContext $clinicContext): void
    {
        $this->redirectUrl = url()->current();
        $this->loadContext($clinicContext);
    }

    public function switchClinic($value)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            return;
        }

        $clinicContext = app(ClinicContext::class);

        if ($value === 'all') {
            $clinicContext->setClinic($user, null);
            $this->viewingAllClinics = true;
        } else {
            $clinicContext->setClinic($user, (int) $value);
            $this->viewingAllClinics = false;
        }


        // IMPORTANT: don't cast the string 'all' to int (it becomes 0).
        // Always compute the selected clinic id based on the updated context.
        $selectedClinicId = $this->viewingAllClinics
            ? null
            : $clinicContext->currentClinicId($user);

        $payload = [
            'clinicId' => $selectedClinicId,
            'viewingAllClinics' => $this->viewingAllClinics,
        ];

        // Livewire v3: global event
        $this->dispatch('clinicContextChanged', $payload);

        // Livewire v3 note: emitTo() no longer exists.
        // We rely on the browser-event + full page reload to refresh all clinic-scoped UI.

        $clinicName = $this->viewingAllClinics
            ? 'All Clinics'
            : optional($clinicContext->currentClinic($user))->name;

        if (!$clinicName) {
            $clinicName = 'Selected Clinic';
        }

        session()->flash('success', "Clinic switched to {$clinicName}.");

    // Trigger a full page reload (layout listens for this browser event).
        $this->dispatch('clinic-context-refreshed', $payload);

        // Don't do an immediate server-side redirect here.
        // The layout listens for `clinic-context-refreshed` and will reload the page.
        // (A short delay is applied client-side so console logs are visible.)
        return;
    }

    public function loadContext(?ClinicContext $clinicContext = null): void
    {
        $clinicContext = $clinicContext ?: app(ClinicContext::class);
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            $this->clinicOptions = [];
            $this->clinicId = null;
            $this->viewingAllClinics = false;
            return;
        }

        $clinicContext->initialize($user);

        $this->clinicOptions = $clinicContext->availableClinics($user)
            ->map(fn ($clinic) => ['id' => $clinic->id, 'name' => $clinic->name])
            ->toArray();

        $this->viewingAllClinics = $clinicContext->isAllClinicsSelection($user);
        $currentClinicId = $clinicContext->currentClinicId($user);

        $this->clinicId = $this->viewingAllClinics ? 'all' : ($currentClinicId ? (string) $currentClinicId : null);
    }

    public function render()
    {
        return view('livewire.clinic-switcher');
    }
}
