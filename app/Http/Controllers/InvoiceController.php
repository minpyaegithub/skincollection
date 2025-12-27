<?php

namespace App\Http\Controllers;

use App\Services\ClinicContext;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'clinic.context']);
    }

    public function index(ClinicContext $clinicContext)
    {
        $clinicContext->initialize(auth()->user());

        return view('invoice.index');
    }
}
