<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Ensure the user is authenticated.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'clinic.context']);
    }

    public function index()
    {
        return view('appointment.index');
    }
}
