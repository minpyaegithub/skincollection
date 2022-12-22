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
use Input;

class ReportController extends Controller
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
        return view('report.profit-loss');
    }

    public function getPfData(Request $request){
        $from = $request->from_date;
        $to = $request->to_date;

        $sale_query = 'select SUM(price*qty) sale_revenues, SUM(purchase_price*qty) purchase_revenues FROM sales where created_time BETWEEN "'.$from.'" AND "'.$to.'"';
        $sales = DB::select($sale_query);

        $total_query = 'select SUM(sub_total) total_revenues from invoices where created_time BETWEEN "'.$from.'" AND "'.$to.'"';
        $tatal_revenues = DB::select($total_query);

        $expense_query = 'select category, amount from expenses where created_time BETWEEN "'.$from.'" AND "'.$to.'"';
        $expenses = DB::select($expense_query);
        
        $data = [
            'sale_revenues' => $sales[0]->sale_revenues,
            'purchase_revenues' => $sales[0]->purchase_revenues,
            'total_revenues' => $tatal_revenues[0]->total_revenues,
        ];

        return response()->json([$data,$expenses]);
    }

    public function profile(Patient $patient)
    {
        $patient_weight_query = 'select DISTINCT DATE(created_time) date,weight FROM weights WHERE patient_id="'.$patient->id.'" and MONTH(created_time) = MONTH(CURRENT_DATE()) AND YEAR(created_time) = YEAR(CURRENT_DATE()) GROUP BY DATE(created_time) ORDER BY created_time asc';
        $patient_weight = DB::select($patient_weight_query);

        $query = 'select id,count(*) as count,invoice_no,price,SUM(sub_total) total,type, DATE_FORMAT(created_time, "%d %M %Y") created_time FROM invoices WHERE patient_id="'.$patient->id.'" and type="treatment" GROUP BY invoice_no ORDER BY created_time asc ';
        $invoices = DB::select($query);

        $photo_query = 'select photo.id, photo.patient_id, photo.photo, DATE_FORMAT(photo.created_time, "%d %M %Y") created_time FROM photos photo WHERE photo.patient_id="'.$patient->id.'" GROUP BY photo.created_time ORDER BY photo.created_time desc ';
        $photos = DB::select($photo_query);

        $record_query = 'select id, description, DATE_FORMAT(created_time, "%d %M %Y") created_time FROM photos WHERE patient_id="'.$patient->id.'"  ORDER BY created_time desc ';
        $records = DB::select($record_query);
        //dd($photos);

        return view('patients.profile')->with(['patient'  => $patient, 'patient_weight'=> $patient_weight, 'invoices'=>$invoices, 'photos'=>$photos, 'records'=>$records ]);
    }

}
