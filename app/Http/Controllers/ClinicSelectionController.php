<?php

namespace App\Http\Controllers;

use App\Services\ClinicContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClinicSelectionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function update(Request $request, ClinicContext $clinicContext)
    {
        $user = $request->user();

        $availableClinicIds = $clinicContext->availableClinics($user)->pluck('id')->map(fn ($id) => (string) $id)->all();

        $validated = $request->validate([
            'clinic_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($availableClinicIds) {
                    if ($value !== 'all' && !in_array($value, $availableClinicIds, true)) {
                        $fail('Invalid clinic selection.');
                    }
                },
            ],
        ]);

        $clinicId = $validated['clinic_id'];

        if ($clinicId === 'all') {
            $clinicContext->setClinic($user, null);
        } else {
            $clinicContext->setClinic($user, (int) $clinicId);
        }

        return back()->with('success', 'Clinic selection updated.');
    }
}
