<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Services\ClinicContext;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Purchase;
use App\Models\Pharmacy;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(private ClinicContext $clinicContext)
    {
        $this->middleware(['auth']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();

        $this->clinicContext->initialize($user);
        $clinicId = $this->clinicContext->currentClinicId($user);

        $patientQuery = Patient::query();
        $appointmentQuery = Appointment::query();

        if ($clinicId) {
            $patientQuery->where('clinic_id', $clinicId);
            $appointmentQuery->where('clinic_id', $clinicId);
        }

    $total_patient = (clone $patientQuery)->count();
    $today_patient = (clone $patientQuery)->whereDate('created_at', today())->count();
    $total_appointment = (clone $appointmentQuery)->count();
    $today_appointment = (clone $appointmentQuery)->whereDate('date', today())->count();

        $patient_monthly = Patient::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->when($clinicId, fn ($query) => $query->where('clinic_id', $clinicId))
            ->whereYear('created_at', today()->year)
            ->whereMonth('created_at', today()->month)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return view('home', [
            'total_patient' => [(object)['count' => $total_patient]],
            'today_patient' => [(object)['count' => $today_patient]],
            'total_appointment' => [(object)['count' => $total_appointment]],
            'today_appointment' => [(object)['count' => $today_appointment]],
            'patient_monthly' => $patient_monthly,
            'activeClinic' => $clinicId ? $this->clinicContext->currentClinic($user) : null,
            'viewingAllClinics' => $this->clinicContext->isAllClinicsSelection($user),
        ]);
    }

    public function Inventoryindex()
    {
        $user = auth()->user();

        $this->clinicContext->initialize($user);
        $clinicId = $this->clinicContext->currentClinicId($user);

        $total_purchase = Purchase::query()
            ->when($clinicId, fn ($query) => $query->where('clinic_id', $clinicId))
            ->whereYear('created_at', today()->year)
            ->whereMonth('created_at', today()->month)
            ->sum(DB::raw('net_price * qty'));

        $total_sale = InvoiceItem::query()
            ->when($clinicId, fn ($query) => $query->where('clinic_id', $clinicId))
            ->where('item_type', InvoiceItem::TYPE_SALE)
            ->whereYear('created_at', today()->year)
            ->whereMonth('created_at', today()->month)
            ->sum('subtotal');

        $total_stock = Pharmacy::query()
            ->when($clinicId, fn ($query) => $query->where('clinic_id', $clinicId))
            ->count();

        $out_of_stock = DB::table('out_of_stocks')
            ->when($clinicId, fn ($query) => $query->where('clinic_id', $clinicId))
            ->whereRaw('total - sale <= 0')
            ->count();

        $today_income = Invoice::query()
            ->when($clinicId, fn ($query) => $query->where('clinic_id', $clinicId))
            ->whereDate('invoice_date', today())
            ->sum('total_amount');

        $sale_monthly = InvoiceItem::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(subtotal) as total'))
            ->when($clinicId, fn ($query) => $query->where('clinic_id', $clinicId))
            ->where('item_type', InvoiceItem::TYPE_SALE)
            ->whereYear('created_at', today()->year)
            ->whereMonth('created_at', today()->month)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $stock_details = DB::table('pharmacies as phar')
            ->select('phar.name', DB::raw('(total - sale) as available_qty'), 'pur.qty', 'pur.created_at', 'pur.updated_at')
            ->when($clinicId, fn ($query) => $query->where('phar.clinic_id', $clinicId))
            ->leftJoin('out_of_stocks as ostock', 'phar.id', '=', 'ostock.phar_id')
            ->leftJoin('purchases as pur', 'phar.id', '=', 'pur.phar_id')
            ->groupBy('phar.id')
            ->orderBy('available_qty', 'asc')
            ->get();

        return view('inventory-home', [
            'total_purchase' => [(object)['total' => $total_purchase ?? 0]],
            'total_sale' => [(object)['total' => $total_sale]],
            'total_stock' => [(object)['total' => $total_stock]],
            'out_of_stock' => $out_of_stock,
            'sale_monthly' => $sale_monthly,
            'stock_details' => $stock_details,
            'today_income' => [(object)['sub_total' => $today_income]],
            'activeClinic' => $clinicId ? $this->clinicContext->currentClinic($user) : null,
            'viewingAllClinics' => $this->clinicContext->isAllClinicsSelection($user),
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
