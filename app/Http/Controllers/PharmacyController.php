<?php

namespace App\Http\Controllers;

class PharmacyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'clinic.context']);
    }

    public function index()
    {
        return view('pharmacy.index');
    }
}
