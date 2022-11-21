<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Yajra\Datatables\Datatables;
use Input;

class DashboardController extends Controller
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

    public function index()
    {
        //$purchase = Purchase::all();
        $query = 'SELECT w.id, patient.token, patient.first_name, patient.last_name, w.weight, DATE_FORMAT(w.created_time,"%d-%m-%Y") AS created_time FROM weights w LEFT JOIN patients patient on w.patient_id = patient.id ORDER BY w.created_time DESC';
        $weights = DB::select($query);
        return view('weight.index', ['weights' => $weights]);
    }
    
    
}
