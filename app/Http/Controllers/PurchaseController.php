<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Exports\PurchaseExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Yajra\Datatables\Datatables;
use Input;

class PurchaseController extends Controller
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
        $purchase = Purchase::all();
        return view('purchase.index', ['purchase' => $purchase]);
    }
    
    public function create()
    {
       
        return view('purchase.add');
    }

    public function store(Request $request)
    {
        $created_time = date("Y-m-d", strtotime($request->created_time)); 

        // Validations
        $request->validate([
            'name'    => 'required',
            'selling_price' => 'required|numeric',
            'net_price' => 'required|numeric',
            'qty' => 'required|numeric',
            'created_time'     => 'required'
        ]);

         DB::beginTransaction();
        try {

            $purchase = Purchase::create([
                'name'          => $request->name,
                'selling_price' => $request->selling_price,
                'net_price'     => $request->net_price,
                'qty'           => $request->qty,
                'created_time'   => $created_time
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('purchase.index')->with('success','Purchase Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function edit(Purchase $purchase)
    {
        return view('purchase.edit')->with(['purchase'  => $purchase ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $created_time = date("Y-m-d", strtotime($request->created_time)); 
        // Validations
        $request->validate([
            'name'    => 'required',
            'selling_price' => 'required|numeric',
            'net_price' => 'required|numeric',
            'qty' => 'required|numeric',
            'created_time'     => 'required'
        ]);

        DB::beginTransaction();
        try {

            // Store Data
            $purchase_updated = Purchase::whereId($purchase->id)->update([
                'name'          => $request->name,
                'selling_price' => $request->selling_price,
                'net_price'     => $request->net_price,
                'qty'           => $request->qty,
                'created_time'   => $created_time
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('purchase.index')->with('success','Purchase Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function delete(Purchase $purchase)
    {
        DB::beginTransaction();
        try {
            // Delete Patient
            $purchase = Purchase::whereId($purchase->id)->delete();

            DB::commit();
            return $purchase;
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
    public function importPurchase()
    {
        return view('purchase.import');
    }

    public function uploadPurchase(Request $request)
    {
        Excel::import(new PurchaseImport, $request->file);
        
        return redirect()->route('purchase.index')->with('success', 'Purchase Imported Successfully');
    }

    public function export() 
    {
        return Excel::download(new PurchaseExport, 'purchase.xlsx');
    }

}
