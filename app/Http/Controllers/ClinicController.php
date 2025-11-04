<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClinicController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the clinics.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('clinics.index');
    }
}