<?php

namespace App\Http\Controllers;

use App\Models\Record;
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

class PatientRecordController extends Controller
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
        $query = 'SELECT record.id, patient.token, patient.first_name, patient.last_name, record.description, DATE_FORMAT(record.created_time,"%d-%m-%Y") AS created_time FROM records record LEFT JOIN patients patient on record.patient_id = patient.id ORDER BY record.created_time DESC';
        $records = DB::select($query);
        return view('patient-record.index', ['records' => $records]);
    }
    
    public function create()
    {
        $patients = Patient::all();
        return view('patient-record.add', ['patients' => $patients]);
    }

    public function store(Request $request)
    {
        $created_time = date("Y-m-d", strtotime($request->created_time)); 
        // Validations
        $request->validate([
            'patient_id' => 'required',
            'created_time'     => 'required'
        ]);

         DB::beginTransaction();
        try {

            $record = Record::create([
                'patient_id'       => $request->patient_id,
                'description' => $request->description,
                'created_time'  => $created_time
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('record.index')->with('success','Patient Record Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function edit(Record $record)
    {
        $patients = Patient::all();
        return view('patient-record.edit')->with(['record'  => $record, 'patients' => $patients ]);
    }

    public function update(Request $request, Record $record)
    {
        $created_time = date("Y-m-d", strtotime($request->created_time)); 
        // Validations
        $request->validate([
            'patient_id' => 'required',
            'created_time'     => 'required'
        ]);

        DB::beginTransaction();
        try {

            // Store Data
            $record_updated = Record::whereId($record->id)->update([
                'patient_id'       => $request->patient_id,
                'description' => $request->description,
                'created_time'  => $created_time
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('record.index')->with('success','Record Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function delete(Record $record)
    {
        DB::beginTransaction();
        try {
            // Delete Patient
            $record = Record::whereId($record->id)->delete();

            DB::commit();
            return $record;
            //return redirect()->route('patients.index')->with('success', 'Patient Deleted Successfully!.');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
