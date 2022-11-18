<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Treatment;
use App\Models\Patient;
use App\Models\Sale;
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

class InvoiceController extends Controller
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
        $query = "select id,count(*) as count,invoice_no,price,SUM(sub_total) total,type, DATE_FORMAT(created_time, '%d %M %Y') created_time FROM invoices GROUP BY invoice_no";
        $invoices = DB::select($query);
        //dd(DB::getQueryLog());
        //return datatables($patients)->toJson();
        return view('invoice.index', ['invoices' => $invoices]);
    }
    
    public function create()
    {
        $treatments = Treatment::orderBy('name', 'asc')->get();
        $patients = Patient::orderBy('first_name', 'asc')->get();
        $pharmacies = Pharmacy::orderBy('name', 'asc')->get();
        $invoice_number = $this->invoiceNumber();
        return view('invoice.add', 
        ['patients' => $patients, 
            'treatments' => $treatments, 
            'invoice_number'=> $invoice_number, 
            'pharmacies' => $pharmacies
        ]);
    }

    function invoiceNumber()
    {
        $latest = Invoice::latest()->first();

        if (! $latest) {
            return 'SKC0001';
        }

        $string = preg_replace("/[^0-9\.]/", '', $latest->invoice_no);

        return 'SKC' . sprintf('%04d', $string+1);
    }

    public function store(Request $request)
    {
        $tbl_values = $request->tbl_values;
        $created_time = date("Y-m-d", strtotime($request->invoice_date)); 
        
        DB::beginTransaction();
        try {
            
                if($tbl_values){
                    if($request->type == 'treatment'){
                        foreach($tbl_values as $tbl_value){
                            $treatment_id = $tbl_value['select_treatment'];
                            Invoice::create([
                                'invoice_no'    => $request->invoice_no,
                                'patient_id'    => $request->patient_id,
                                'created_time'  => $created_time,
                                'type'          => $request->type,
                                'treatment_id'  => $treatment_id,
                                'price'  => $tbl_value['price'],
                                'discount'   => $tbl_value['discount'],
                                'sub_total'     => $tbl_value['sub_total'],
                                'discount_type' => $tbl_value['discount_type']
                            ]);

                            $query = "select * from treatment_packages where treatment_id="."'$treatment_id'";
                            $treatment_packages = DB::select($query);

                            foreach($treatment_packages as $treatment_package){
                                Sale::create([
                                    'invoice_no'    => $request->invoice_no,
                                    'phar_id'  => $treatment_package->phar_id,
                                    'qty'  => $treatment_package->qty,
                                    'price'  => $tbl_value['price'],
                                    'created_time'  => $created_time,
                                ]);
                            }
                        }
                    }else{
                        foreach($tbl_values as $tbl_value){
                            Invoice::create([
                                'invoice_no'    => $request->invoice_no,
                                'created_time'  => $created_time,
                                'type'          => $request->type,
                                'phar_id'  => $tbl_value['select_pharmacy'],
                                'qty'  => $tbl_value['qty'],
                                'price'  => $tbl_value['price'],
                                'discount'   => $tbl_value['discount'],
                                'sub_total'     => $tbl_value['sub_total'],
                                'discount_type' => $tbl_value['discount_type']
                            ]);

                            Sale::create([
                                'invoice_no'    => $request->invoice_no,
                                'phar_id'  => $tbl_value['select_pharmacy'],
                                'qty'  => $tbl_value['qty'],
                                'price'  => $tbl_value['price'],
                                'created_time'  => $created_time,
                            ]);
                        }
                    }
                    
                }

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

    public function edit(Invoice $invoice)
    {
        return view('invoice.edit')->with(['invoices'  => $invoice ]);
    }

    public function update(Request $request, Invoice $invoice)
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
            'inches'    => 'required',
            'photo.*' => 'image|mimes:jpeg,png,jpg,gif,svg'
            //'disease'   => 'required'
        ]);

        DB::beginTransaction();
        try {   
            // Store Data
            $invoice_updated = Invoice::whereId($invoice->id)->update([
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
                'photo'         => json_encode($image_all)
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('invoices.index')->with('success','Invoice Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function delete($invoice_no)
    {
        DB::beginTransaction();
        try {
            // Delete Patient
            $invoice = Invoice::where('invoice_no', '=' ,$invoice_no)->delete();
            $sale = Sale::where('invoice_no', '=' ,$invoice_no)->delete();

            DB::commit();
            return $invoice_no;
            //return redirect()->route('patients.index')->with('success', 'Patient Deleted Successfully!.');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

}
