<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Yajra\Datatables\Datatables;
use Input;

class ExpenseController extends Controller
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
        $expense = Expense::orderBy('created_time', 'DESC')->get();
        return view('expense.index')->with(['expense'  => $expense]);
    }
    
    public function create()
    {
        return view('expense.add');
    }

    public function store(Request $request)
    {
        $created_time = date("Y-m-d", strtotime($request->created_time)); 
        // Validations
        $request->validate([
            'category' => 'required',
            'amount' => 'required|numeric',
            'created_time'     => 'required'
        ]);

         DB::beginTransaction();
        try {

            $expense = Expense::create([
                'category'       => $request->category,
                'amount' => $request->amount,
                'description'     => $request->description,
                'created_time'  => $created_time
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('expense.index')->with('success','Expense Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function edit(Expense $expense)
    {
        return view('expense.edit')->with(['expense'  => $expense]);
    }

    public function update(Request $request, Expense $expense)
    {
        $created_time = date("Y-m-d", strtotime($request->created_time)); 
        // Validations
        $request->validate([
            'category' => 'required',
            'amount' => 'required|numeric',
            'created_time'     => 'required'
        ]);

        DB::beginTransaction();
        try {

            // Store Data
            $expense_updated = Expense::whereId($expense->id)->update([
                'category' => $request->category,
                'amount'     => $request->amount,
                'description'           => $request->description,
                'created_time'  => $created_time
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('expense.index')->with('success','Expense Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function delete(Expense $expense)
    {
        DB::beginTransaction();
        try {
            // Delete Patient
            $expene = Expense::whereId($expense->id)->delete();

            DB::commit();
            return $expense;
            //return redirect()->route('patients.index')->with('success', 'Patient Deleted Successfully!.');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

}
