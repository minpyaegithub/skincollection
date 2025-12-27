<?php

namespace App\Http\Controllers;

use App\Exports\TreatmentExport;
use App\Services\ClinicContext;
use Maatwebsite\Excel\Facades\Excel;

class TreatmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'clinic.context']);
    }

    public function index(ClinicContext $clinicContext)
    {
        $clinicContext->initialize(auth()->user());

        return view('treatment.index');
    }

    public function export(ClinicContext $clinicContext)
    {
        $user = auth()->user();
        $clinicContext->initialize($user);

        $clinicId = $clinicContext->isAllClinicsSelection($user)
            ? null
            : $clinicContext->currentClinicId($user);

        return Excel::download(new TreatmentExport($clinicId), 'treatment.xlsx');
    }
}
