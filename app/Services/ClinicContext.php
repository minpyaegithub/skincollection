<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class ClinicContext
{
    private const SESSION_KEY = 'clinic_context.selected';
    private const ALL_CLINICS = 'all';

    /**
     * Ensure the clinic context is initialised for the authenticated user.
     */
    public function initialize(User $user): void
    {
        if ($user->isAdmin()) {
            if (!Session::has(self::SESSION_KEY)) {
                if ($user->clinic_id) {
                    Session::put(self::SESSION_KEY, (int) $user->clinic_id);
                } else {
                    $firstClinic = $this->availableClinics($user)->first();
                    if ($firstClinic) {
                        Session::put(self::SESSION_KEY, (int) $firstClinic->id);
                    } else {
                        Session::put(self::SESSION_KEY, self::ALL_CLINICS);
                    }
                }
            }

            return;
        }

        // Non-admins are always fixed to their assigned clinic.
        Session::put(self::SESSION_KEY, $user->clinic_id);
    }

    /**
     * Retrieve the clinics the user can work with.
     */
    public function availableClinics(User $user): Collection
    {
        if ($user->isAdmin()) {
            return Clinic::orderBy('name')->get();
        }

        $clinic = $user->clinic;

        return $clinic ? collect([$clinic]) : collect();
    }

    /**
     * Return the current clinic id or null when viewing all.
     */
    public function currentClinicId(User $user): ?int
    {
        if ($this->isAllClinicsSelection($user)) {
            return null;
        }

        $value = Session::get(self::SESSION_KEY, null);

        if ($value === null) {
            return $user->clinic_id;
        }

        return (int) $value;
    }

    /**
     * Return the currently selected clinic model.
     */
    public function currentClinic(User $user): ?Clinic
    {
        $clinicId = $this->currentClinicId($user);

        if (!$clinicId) {
            return null;
        }

        return Clinic::find($clinicId);
    }

    /**
     * Update the clinic selection for the user.
     */
    public function setClinic(User $user, ?int $clinicId): void
    {
        if ($user->isAdmin()) {
            if ($clinicId === null) {
                Session::put(self::SESSION_KEY, self::ALL_CLINICS);
            } else {
                Session::put(self::SESSION_KEY, (int) $clinicId);
            }

            return;
        }

        // Non-admins cannot change clinic context manually.
        Session::put(self::SESSION_KEY, $user->clinic_id);
    }

    /**
     * Determine whether the admin is viewing all clinics.
     */
    public function isAllClinicsSelection(User $user): bool
    {
        return $user->isAdmin() && Session::get(self::SESSION_KEY) === self::ALL_CLINICS;
    }

    /**
     * Clear the stored clinic selection.
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
