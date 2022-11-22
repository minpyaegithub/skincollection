<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $total_patient_query = 'select count(*) count from patients';
        $total_patient = DB::select($total_patient_query);

        $today_patient_query = 'select count(*) count FROM patients where DATE(created_at) = CURDATE()';
        $today_patient = DB::select($today_patient_query);

        $total_appointment_query = 'select count(*) count from appointments';
        $total_appointment = DB::select($total_appointment_query);

        $today_appointment_query = 'select count(*) count FROM appointments where DATE(date) = CURDATE()';
        $today_appointment = DB::select($today_appointment_query);

        $patient_monthly_query = 'select DISTINCT DATE(created_at) date,count(*) count, created_at FROM patients WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) GROUP BY DATE(created_at) ORDER BY created_at asc';
        $patient_monthly = DB::select($patient_monthly_query);
        //dd($patient_monthly);
        return view('home', [
            'total_patient' => $total_patient,
            'today_patient' => $today_patient,
            'total_appointment' => $total_appointment,
            'today_appointment' => $today_appointment,
            'patient_monthly' => $patient_monthly,
        ]);
    }

    public function Inventoryindex()
    {
        $total_purchase_query = 'select SUM(net_price * qty) AS total FROM purchases WHERE MONTH(created_time) = MONTH(CURRENT_DATE()) AND YEAR(created_time) = YEAR(CURRENT_DATE()) ORDER BY created_time asc';
        $total_purchase = DB::select($total_purchase_query);

        $total_sale_query = 'select SUM(price * qty) AS total FROM sales WHERE MONTH(created_time) = MONTH(CURRENT_DATE()) AND YEAR(created_time) = YEAR(CURRENT_DATE()) ORDER BY created_time asc';
        $total_sale = DB::select($total_sale_query);

        $total_stock_query = 'select count(*) total from pharmacies';
        $total_stock = DB::select($total_stock_query);

        $out_of_stock_query = 'select SUM(total - sale) qty FROM `out_of_stocks` GROUP BY phar_id';
        $out_of_stocks = DB::select($out_of_stock_query);
        $out_of_stock = 0;
        foreach($out_of_stocks as $count){
            if($count->qty == 0){
                $out_of_stock++;
            }
        }

        $sale_monthly_query = 'select DISTINCT DATE(created_time) date,SUM(price * qty) AS total FROM sales WHERE MONTH(created_time) = MONTH(CURRENT_DATE()) AND YEAR(created_time) = YEAR(CURRENT_DATE()) GROUP BY DATE(created_time) ORDER BY created_time asc';
        $sale_monthly = DB::select($sale_monthly_query);

        $stock_detail_query = 'select phar.name, SUM(total - sale) available_qty, pur.qty, pur.created_time, pur.updated_at FROM pharmacies phar LEFT JOIN out_of_stocks ostock ON phar.id=ostock.phar_id LEFT JOIN purchases pur ON phar.id=pur.phar_id GROUP BY phar.id ORDER BY available_qty asc';
        $stock_details = DB::select($stock_detail_query);
        //dd($patient_monthly);
        return view('inventory-home', [
            'total_purchase' => $total_purchase,
            'total_sale' => $total_sale,
            'total_stock' => $total_stock,
            'out_of_stock' => $out_of_stock,
            'sale_monthly' => $sale_monthly,
            'stock_details' => $stock_details,
        ]);
    }

    /**
     * User Profile
     * @param Nill
     * @return View Profile
     * @author Shani Singh
     */
    public function getProfile()
    {
        return view('profile');
    }

    /**
     * Update Profile
     * @param $profileData
     * @return Boolean With Success Message
     * @author Shani Singh
     */
    public function updateProfile(Request $request)
    {
        #Validations
        $request->validate([
            'first_name'    => 'required',
            'last_name'     => 'required',
            'mobile_number' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();
            
            #Update Profile Data
            User::whereId(auth()->user()->id)->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile_number' => $request->mobile_number,
            ]);

            #Commit Transaction
            DB::commit();

            #Return To Profile page with success
            return back()->with('success', 'Profile Updated Successfully.');
            
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    /**
     * Change Password
     * @param Old Password, New Password, Confirm New Password
     * @return Boolean With Success Message
     * @author Shani Singh
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        try {
            DB::beginTransaction();

            #Update Password
            User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);
            
            #Commit Transaction
            DB::commit();

            #Return To Profile page with success
            return back()->with('success', 'Password Changed Successfully.');
            
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }
}
