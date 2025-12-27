<?php

namespace App\Http\Controllers;

use App\Exports\PurchaseExport;
use App\Services\ClinicContext;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'clinic.context', 'role:admin']);
    }

    public function index()
    {
        return view('purchase.index');
    }

    public function export(ClinicContext $clinicContext)
    {
        $user = auth()->user();
        $clinicContext->initialize($user);

        $clinicId = $clinicContext->isAllClinicsSelection($user)
            ? null
            : $clinicContext->currentClinicId($user);

        return Excel::download(new PurchaseExport($clinicId), 'purchase.xlsx');
    }
}
