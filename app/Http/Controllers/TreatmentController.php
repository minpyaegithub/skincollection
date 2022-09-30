<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\Pharmacy;
use App\Models\TreatmentPackage;
use App\Exports\TreatmentExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Yajra\Datatables\Datatables;
use Input;

class TreatmentController extends Controller
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
        $treatment = Treatment::all();
        
        return view('treatment.index', ['treatment' => $treatment]);
    }
    
    public function create()
    {
        $pharmacy = Pharmacy::all();
        return view('treatment.add',['pharmacy' => $pharmacy]);
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $name = $request->name;
        $price = $request->price;
        $tbl_values = $request->tbl_values;
        $price = $request->price;
        $phar_id;$qty=0;
        //dd($tbl_values);
        
        // Validations
        $request->validate([
            'name'    => 'required',
            'price'   => 'required'
        ]);

         DB::beginTransaction();
        try {

            $treatment = Treatment::create([
                'name' => $name,
                'price' => $price,
            ]);
            
            $treatment_id = $treatment->id;

            if($tbl_values){
                foreach($tbl_values as $tbl_value){
                    $phar_id = $tbl_value['phar_id'];
                    $qty = $tbl_value['qty'];

                    $treatment_package = TreatmentPackage::create([
                        'treatment_id' => $treatment_id,
                        'phar_id'      => $phar_id,
                        'qty'          => $qty
        
                    ]);
                }
            }

            

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('treatment.index')->with('success','Treatment Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function edit(Treatment $treatment)
    {
        return view('treatment.edit')->with(['treatment'  => $treatment ]);
    }

    public function update(Request $request, Treatment $treatment)
    {
        // Validations
        $request->validate([
            'name'    => 'required'
        ]);

        DB::beginTransaction();
        try {

            // Store Data
            $pharmacy_updated = Treatment::whereId($treatment->id)->update([
                'name' => $request->name,
                'type' => $request->type,
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('treatment.index')->with('success','Treatment Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function delete(Treatment $treatment)
    {
        DB::beginTransaction();
        try {

            $treatment = Treatment::whereId($treatment->id)->delete();

            DB::commit();
            return $treatment;
            //return redirect()->route('patients.index')->with('success', 'Patient Deleted Successfully!.');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function export() 
    {
        return Excel::download(new TreatmentExport, 'treatment.xlsx');
    }

}
