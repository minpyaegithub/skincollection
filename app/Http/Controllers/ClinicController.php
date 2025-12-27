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
        if (!auth()->user()?->isAdmin()) {
            abort(403);
        }

        return view('clinics.index');
    }
}