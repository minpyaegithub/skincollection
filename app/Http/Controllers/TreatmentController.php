<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\TreatmentPackage;
use App\Models\Pharmacy;
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

    public function saveIndex(){
        $treatment = Treatment::all();
        return redirect()->route('treatment.index',['treatment' => $treatment])->with('success','Treatment Created Successfully.');
    }

    public function updateIndex(){
        $treatment = Treatment::all();
        return redirect()->route('treatment.index',['treatment' => $treatment])->with('success','Treatment Updated Successfully.');
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
            return response()->json([
                'message' => 'success'
            ]);
            //return redirect()->route('treatment.index')->with('success','Treatment Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return response()->json([
                'message' => 'fail'
            ]);
            //return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function edit(Treatment $treatment)
    {
        $pharmacy = Pharmacy::all();
        $treatment_phar = TreatmentPackage::join('pharmacies', 'pharmacies.id', '=', 'treatment_packages.phar_id')
                            ->where('treatment_packages.treatment_id', '=', $treatment->id)
                            ->get(['pharmacies.name', 'treatment_packages.phar_id', 'treatment_packages.qty']);

        return view('treatment.edit')->with(['treatment'  => $treatment, 'pharmacy'=>$pharmacy, 'treatment_phar'=> $treatment_phar]);
    }

    public function update(Request $request, Treatment $treatment)
    {

        DB::beginTransaction();
        try {

            // Store Data
            $name = $request->name;
            $price = $request->price;
            $tbl_values = $request->tbl_values;
            $price = $request->price;
            $phar_id;$qty=0;

            $treatment_updated = Treatment::whereId($treatment->id)->update([
                'name' => $name,
                'price' => $price,
            ]);
            if($tbl_values){
                foreach($tbl_values as $tbl_value){
                    $phar_id = $tbl_value['phar_id'];
                    $qty = $tbl_value['qty'];

                    $treatment_phar = TreatmentPackage::where('treatment_id', '=', $treatment->id)->where('phar_id', '=', $phar_id)->first();
                    if($treatment_phar === null){
                        TreatmentPackage::create([
                            'treatment_id' => $treatment->id,
                            'phar_id'      => $phar_id,
                            'qty'          => $qty
            
                        ]);
                    } else {
                        TreatmentPackage::where('phar_id', $phar_id)->update([
                            'phar_id'  => $phar_id,
                            'qty'      => $qty
                        ]);
                    }

                    
                }
            }

            // Commit And Redirected To Listing
            DB::commit();
            return response()->json([
                'message' => 'success'
            ]);

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return response()->json([
                'message' => 'fail'
            ]);
        }
    }

    public function delete(Treatment $treatment)
    {
        DB::beginTransaction();
        try {

            Treatment::whereId($treatment->id)->delete();
            TreatmentPackage::where('treatment_id', $treatment->id)->delete();

            DB::commit();
            return response()->json([
                'message' => 'success'
            ]);
            //return redirect()->route('patients.index')->with('success', 'Patient Deleted Successfully!.');

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'fail'
            ]);
        }
    }

    public function export() 
    {
        return Excel::download(new TreatmentExport, 'treatment.xlsx');
    }

}
