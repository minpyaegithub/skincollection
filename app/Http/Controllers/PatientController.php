<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Exports\PatientsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Yajra\Datatables\Datatables;

class PatientController extends Controller
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
        //DB::enableQueryLog();
        $patients = Patient::all();
        //dd(DB::getQueryLog());
        //return datatables($patients)->toJson();
        return view('patients.index', ['patients' => $patients]);
    }
    
    public function create()
    {
       
        return view('patients.add');
    }

    public function store(Request $request)
    {
        // Validations
        
        $token = rand(000000,999999);
        $created_time = date("Y-m-d", strtotime($request->dob)); 

        $request->validate([
            'first_name'    => 'required',
            'last_name'     => 'required',
            //'email'         => 'required|unique:users,email',
            'phone' => 'required|numeric',
            'gender'    => 'required',
            'dob'     => 'required',
            'address'    => 'required',
            'weight'     => 'required',
            'feet'     => 'required',
            'inches'    => 'required'
            //'disease'   => 'required'
        ]);

         DB::beginTransaction();
        try {

            // Store Data
            $user = Patient::create([
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'gender'        => $request->gender,
                'dob'           => $created_time,
                'address'       => $request->address,
                'weight'        => $request->weight,
                'feet'          => $request->feet,
                'inches'        => $request->inches,
                'disease'       => $request->disease,
                'token'         => $token
            ]);    

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('patients.index')->with('success','Patient Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function updateStatus($user_id, $status)
    {
        // Validation
        $validate = Validator::make([
            'user_id'   => $user_id,
            'status'    => $status
        ], [
            'user_id'   =>  'required|exists:users,id',
            'status'    =>  'required|in:0,1',
        ]);

        // If Validations Fails
        if($validate->fails()){
            return redirect()->route('users.index')->with('error', $validate->errors()->first());
        }

        try {
            DB::beginTransaction();

            // Update Status
            User::whereId($user_id)->update(['status' => $status]);

            // Commit And Redirect on index with Success Message
            DB::commit();
            return redirect()->route('users.index')->with('success','User Status Updated Successfully!');
        } catch (\Throwable $th) {

            // Rollback & Return Error Message
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit')->with(['patient'  => $patient ]);
    }

    public function update(Request $request, Patient $patient)
    {
        $created_time = date("Y-m-d", strtotime($request->dob));
        // Validations
        $request->validate([
            'first_name'    => 'required',
            'last_name'     => 'required',
            //'email'         => 'required|unique:users,email',
            'phone' => 'required|numeric',
            'gender'    => 'required',
            'dob'     => 'required',
            'address'    => 'required',
            'weight'     => 'required',
            'feet'     => 'required',
            'inches'    => 'required'
            //'disease'   => 'required'
        ]);

        DB::beginTransaction();
        try {

            // Store Data
            $patient_updated = Patient::whereId($patient->id)->update([
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'gender'        => $request->gender,
                'dob'           => $created_time,
                'address'       => $request->address,
                'weight'        => $request->weight,
                'feet'          => $request->feet,
                'inches'        => $request->inches,
                'disease'       => $request->disease
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('patients.index')->with('success','Patient Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function delete(Patient $patient)
    {
        DB::beginTransaction();
        try {
            // Delete Patient
            dd($patient->id);
            //$patient = Patient::whereId($patient->id)->delete();

            DB::commit();
            return $patient;
            //return redirect()->route('patients.index')->with('success', 'Patient Deleted Successfully!.');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Import Patients 
     * @param Null
     * @return View File
     */
    public function importPatients()
    {
        return view('patients.import');
    }

    public function uploadUsers(Request $request)
    {
        Excel::import(new UsersImport, $request->file);
        
        return redirect()->route('users.index')->with('success', 'User Imported Successfully');
    }

    public function export() 
    {
        return Excel::download(new PatientsExport, 'patients.xlsx');
    }

}
