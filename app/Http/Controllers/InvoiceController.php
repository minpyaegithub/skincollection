<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Treatment;
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
        $invoices = Invoice::all();
        //dd(DB::getQueryLog());
        //return datatables($patients)->toJson();
        return view('invoice.index', ['invoices' => $invoices]);
    }
    
    public function create()
    {
        $treatments = Treatment::orderBy('name', 'asc')->get();
        $patients = Patient::orderBy('first_name', 'asc')->get();
        return view('invoice.add', ['patients' => $patients, 'treatments' => $treatments]);
    }

    public function store(Request $request)
    {
        $token = rand(000000,999999);
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

            $names = [];
            if($request->images)
            {  
                foreach($request->images as $image)
                {
                    ///dd($image);
                    //$destinationPath = 'content_images/';
                    $filename = time().rand(1,99).'_'.$image->getClientOriginalName();
                    $image->move(public_path('patient-photo'), $filename);
                    array_push($names, $filename);          

                }
            }

            $invoice = Invoice::create([
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
                'photo'         => json_encode($names),
                'token'         => $token
            ]);

            

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('invoices.index')->with('success','Invoice Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
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

    public function delete(Invoice $invoice)
    {
        DB::beginTransaction();
        try {
            // Delete Patient
            $invoice = Invoice::whereId($invoice->id)->delete();

            DB::commit();
            return $invoice;
            //return redirect()->route('patients.index')->with('success', 'Patient Deleted Successfully!.');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

}
