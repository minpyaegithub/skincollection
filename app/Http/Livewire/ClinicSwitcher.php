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

        $clinicName = $this->viewingAllClinics
            ? 'All Clinics'
            : optional($clinicContext->currentClinic($user))->name;

        if (!$clinicName) {
            $clinicName = 'Selected Clinic';
        }

        session()->flash('success', "Clinic switched to {$clinicName}.");

        // Force the session to be written to disk NOW, before the redirect response
        // is serialised. Without this, the Livewire AJAX response may be sent back
        // to the browser before PHP's session handler has persisted the new clinic
        // value, so the reloaded page still reads the old value.
        session()->save();

        // Redirect directly — most reliable way to refresh all clinic-scoped UI.
        return redirect(request()->header('Referer') ?: url()->current());
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
