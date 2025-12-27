<?php

namespace App\Http\Middleware;

use App\Services\ClinicContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicContext
{
    public function __construct(private ClinicContext $clinicContext)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $this->clinicContext->initialize($user);

            $availableClinics = $this->clinicContext->availableClinics($user);

            if (!$user->isAdmin() && !$user->clinic_id) {
                abort(403, 'Clinic assignment is required to access this feature.');
            }

            view()->share('activeClinic', $this->clinicContext->currentClinic($user));
            view()->share('viewingAllClinics', $this->clinicContext->isAllClinicsSelection($user));
            view()->share('availableClinics', $availableClinics);
        }

        return $next($request);
    }
}
