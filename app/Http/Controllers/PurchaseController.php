<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Exports\PurchaseExport;
use App\Models\Pharmacy;
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
        //$purchase = Purchase::all();
        $query = 'SELECT pur.id,phar.name, pur.selling_price, pur.net_price, DATE_FORMAT(pur.created_time,"%d-%m-%Y") AS created_time FROM purchases pur LEFT JOIN pharmacies phar on pur.phar_id = phar.id ORDER BY pur.created_time DESC';
        $purchase = DB::select($query);
        return view('purchase.index', ['purchase' => $purchase]);
    }
    
    public function create()
    {
        $pharmacy = Pharmacy::all();
        return view('purchase.add', ['pharmacy' => $pharmacy]);
    }

    public function store(Request $request)
    {
        $created_time = date("Y-m-d", strtotime($request->created_time)); 
        // Validations
        $request->validate([
            'selling_price' => 'required|numeric',
            'net_price' => 'required|numeric',
            'qty' => 'required|numeric',
            'created_time'     => 'required'
        ]);

         DB::beginTransaction();
        try {

            $purchase = Purchase::create([
                'phar_id'       => $request->phar_id,
                'selling_price' => $request->selling_price,
                'net_price'     => $request->net_price,
                'qty'           => $request->qty,
                'created_time'  => $created_time
            ]);

            $pur_latest = Purchase::where('phar_id', $request->phar_id)->orderBy('created_time', 'desc')->first();
            $pharmacy_updated = Pharmacy::whereId($request->phar_id)->update([
                'selling_price' => $pur_latest->selling_price,
                'net_price'     => $pur_latest->net_price
            ]);

            $query = 'select SUM(qty) total, phar_id from purchases where phar_id='.$request->phar_id.' GROUP BY phar_id';
            $stocks = DB::select($query);
            foreach($stocks as $stock){
                DB::table('out_of_stocks')->where('phar_id', $request->phar_id)->update(
                    [
                        'total' => $stock->total
                    ]
                );
            }
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
        $pharmacy = Pharmacy::all();
        return view('purchase.edit')->with(['purchase'  => $purchase, 'pharmacy' => $pharmacy ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $created_time = date("Y-m-d", strtotime($request->created_time)); 
        // Validations
        $request->validate([
            'selling_price' => 'required|numeric',
            'net_price' => 'required|numeric',
            'qty' => 'required|numeric',
            'created_time'     => 'required'
        ]);

        DB::beginTransaction();
        try {

            // Store Data
            $purchase_updated = Purchase::whereId($purchase->id)->update([
                'phar_id'       => $request->phar_id,
                'selling_price' => $request->selling_price,
                'net_price'     => $request->net_price,
                'qty'           => $request->qty,
                'created_time'  => $created_time
            ]);

            $pur_latest = Purchase::where('phar_id', $request->phar_id)->orderBy('created_time', 'desc')->first();
            $pharmacy_updated = Pharmacy::whereId($request->phar_id)->update([
                'selling_price' => $pur_latest->selling_price,
                'net_price'     => $pur_latest->net_price
            ]);

            $query = 'select SUM(qty) total, phar_id from purchases where phar_id='.$request->phar_id.' GROUP BY phar_id';
            $stocks = DB::select($query);
            foreach($stocks as $stock){
                DB::table('out_of_stocks')->where('phar_id', $request->phar_id)->update(
                    [
                        'total' => $stock->total
                    ]
                );
            }

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
            $purchases = Purchase::whereId($purchase->id)->delete();

            $pur_latest = Purchase::where('phar_id', $purchase->phar_id)->orderBy('created_time', 'desc')->first();
            if($pur_latest){
                $pharmacy_updated = Pharmacy::whereId($purchase->phar_id)->update([
                    'selling_price' => $pur_latest->selling_price,
                    'net_price'     => $pur_latest->net_price
                ]);
            }
            

            $query = 'select SUM(qty) total, phar_id from purchases where phar_id='.$purchase->phar_id.' GROUP BY phar_id';
            $stocks = DB::select($query);
            if($stocks){
                $total = $stocks[0]->total;
            }else{
                $total = 0;
            }
            DB::table('out_of_stocks')->where('phar_id', $purchase->phar_id)->update(
                [
                    'total' => $total
                ]
            );

            DB::commit();
            return $purchases;
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
