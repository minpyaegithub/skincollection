<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Exports\PharmacyExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Yajra\Datatables\Datatables;
use Input;

class PharmacyController extends Controller
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
        $pharmacy = Pharmacy::all();
        //dd(DB::getQueryLog());
        //return datatables($patients)->toJson();
        return view('pharmacy.index', ['pharmacy' => $pharmacy]);
    }
    
    public function create()
    {
       
        return view('pharmacy.add');
    }

    public function store(Request $request)
    {
        $expire_date = date("Y-m-d", strtotime($request->expire_date)); 

        // Validations
        $request->validate([
            'name'    => 'required'
        ]);

         DB::beginTransaction();
        try {

            $pharmacy = Pharmacy::create([
                'name'          => $request->name
            ]);

            DB::table('out_of_stocks')->insert(
                [
                    'phar_id' => $pharmacy->id
                ]
            );

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('pharmacy.index')->with('success','Pharmacy Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function edit(Pharmacy $pharmacy)
    {
        return view('pharmacy.edit')->with(['pharmacy'  => $pharmacy ]);
    }

    public function update(Request $request, Pharmacy $pharmacy)
    {
        $expire_date = date("Y-m-d", strtotime($request->expire_date)); 
        // Validations
        $request->validate([
            'name'    => 'required'
        ]);

        DB::beginTransaction();
        try {

            // Store Data
            $pharmacy_updated = Pharmacy::whereId($pharmacy->id)->update([
                'name'          => $request->name
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('pharmacy.index')->with('success','Pharmacy Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function delete(Pharmacy $pharmacy)
    {
        DB::beginTransaction();
        try {
            // Delete Patient
            $phar = Pharmacy::whereId($pharmacy->id)->delete();
            DB::table('out_of_stocks')->where('phar_id','=',$pharmacy->id)->delete();

            DB::commit();
            return $pharmacy;
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
        return view('pharmacy.import');
    }

    public function uploadPharmacy(Request $request)
    {
        Excel::import(new PharmacyImport, $request->file);
        
        return redirect()->route('pharmacy.index')->with('success', 'User Imported Successfully');
    }

    public function export() 
    {
        return Excel::download(new PharmacyExport, 'pharmacy.xlsx');
    }

}
