<?php

namespace App\Http\Controllers;

use App\Models\Weight;
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

class WeightController extends Controller
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
        $patients = Patient::all();
        return view('weight.index', ['weights' => $weights, 'patients' => $patients]);
    }

    public function listByPatient($patient_id)
    {
        $query = '';
        if($patient_id == 'all'){
            $query = 'SELECT w.id, patient.token, patient.first_name, patient.last_name, w.weight, DATE_FORMAT(w.created_time,"%d-%m-%Y") AS created_time FROM weights w LEFT JOIN patients patient on w.patient_id = patient.id  ORDER BY w.created_time DESC';
        }else{
            $query = 'SELECT w.id, patient.token, patient.first_name, patient.last_name, w.weight, DATE_FORMAT(w.created_time,"%d-%m-%Y") AS created_time FROM weights w LEFT JOIN patients patient on w.patient_id = patient.id Where w.patient_id = "'.$patient_id.'"  ORDER BY w.created_time DESC';
        }
        //dd($query);
        
        $weights = DB::select($query);
        return response()->json([$weights]);
    }
    
    public function create()
    {
        $patients = Patient::all();
        return view('weight.add', ['patients' => $patients]);
    }

    public function store(Request $request)
    {
        $created_time = date("Y-m-d", strtotime($request->created_time)); 
        // Validations
        $request->validate([
            'patient_id' => 'required',
            //'weight' => 'required|numeric',
            'created_time'     => 'required'
        ]);

         DB::beginTransaction();
        try {

            $weight = Weight::create([
                'patient_id'       => $request->patient_id,
                'weight' => $request->weight,
                'arm_contract' => $request->arm_contract,
                'arm_relax' => $request->arm_relax,
                'arm_middle' => $request->arm_middle,
                'arm_lower' => $request->arm_lower,
                'waist_upper' => $request->waist_upper,
                'waist_middle' => $request->waist_middle,
                'waist_lower' => $request->waist_lower,
                'thigh_upper' => $request->thigh_upper,
                'thigh_middle' => $request->thigh_middle,
                'thigh_lower' => $request->thigh_lower,
                'calf_upper' => $request->calf_upper,
                'calf_middle' => $request->calf_middle,
                'calf_lower' => $request->calf_lower,
                'created_time'  => $created_time
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('weight.index')->with('success','Weight Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function edit(Weight $weight)
    {
        $patients = Patient::all();
        return view('weight.edit')->with(['weight'  => $weight, 'patients' => $patients ]);
    }

    public function view($weight){
        $query = 'SELECT w.*, patient.token, patient.first_name, patient.last_name, w.weight, DATE_FORMAT(w.created_time,"%d-%m-%Y") AS created_at FROM weights w LEFT JOIN patients patient  on w.patient_id = patient.id Where w.id="'.$weight.'" ORDER BY w.created_time DESC';
        $weights = DB::select($query);
        $patients = Patient::all();
        return view('weight.view', ['weight' => $weights, 'patients' => $patients]);
    }

    public function update(Request $request, Weight $weight)
    {
        $created_time = date("Y-m-d", strtotime($request->created_time)); 
        // Validations
        $request->validate([
            'patient_id' => 'required',
            //'weight' => 'required|numeric',
            'created_time'     => 'required'
        ]);

        DB::beginTransaction();
        try {

            // Store Data
            $weight_updated = Weight::whereId($weight->id)->update([
                'patient_id'       => $request->patient_id,
                'weight' => $request->weight,
                'arm_contract' => $request->arm_contract,
                'arm_relax' => $request->arm_relax,
                'arm_middle' => $request->arm_middle,
                'arm_lower' => $request->arm_lower,
                'waist_upper' => $request->waist_upper,
                'waist_middle' => $request->waist_middle,
                'waist_lower' => $request->waist_lower,
                'thigh_upper' => $request->thigh_upper,
                'thigh_middle' => $request->thigh_middle,
                'thigh_lower' => $request->thigh_lower,
                'calf_upper' => $request->calf_upper,
                'calf_middle' => $request->calf_middle,
                'calf_lower' => $request->calf_lower,
                'created_time'  => $created_time
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('weight.index')->with('success','Weight Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function delete(Weight $weight)
    {
        DB::beginTransaction();
        try {
            // Delete Patient
            $weight = Weight::whereId($weight->id)->delete();

            DB::commit();
            return $weight;
            //return redirect()->route('patients.index')->with('success', 'Patient Deleted Successfully!.');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
