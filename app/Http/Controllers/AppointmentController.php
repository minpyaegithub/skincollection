<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Yajra\Datatables\Datatables;
use Input;
use Carbon\Carbon;

class AppointmentController extends Controller
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
        $apointments = Appointment::all();
        return view('appointments.index', ['appointment' => $appointment]);
    }
    
    public function create()
    {
        $appointments = Appointment::whereDate('date', Carbon::today())->get()->toArray();
        $times = [];
        foreach($appointments as $appointment){
            foreach(explode(',', $appointment['time']) as $time){
                array_push($times, $time);
            }
           
        }
        $filter_time = array_filter($times, 'strlen');
        $unique_time = array_unique($filter_time);
        $appointment_times = AppointmentTime::all();
        return view('appointment.add', ['appointments' => $unique_time, 'appointment_times'=> $appointment_times]);
    }

    public function list(Request $request)
    {
        $date = date("Y-m-d", strtotime($request->date)); 
        $appointments = Appointment::whereDate('date', $date)->get()->toArray();
        $times = [];
        foreach($appointments as $appointment){
            foreach(explode(',', $appointment['time']) as $time){
                array_push($times, $time);
            }
           
        }
        
        $filter_time = array_filter($times, 'strlen');
        $unique_time = array_unique($filter_time);
        return response()->json([json_encode($unique_time)]);
    }

    public function view(Request $request)
    {
        $date = date("Y-m-d", strtotime($request->date)); 
        $time = $request->time;
        $query = 'select * from appointments where DATE(date) = "'.$date.'" and FIND_IN_SET("'.$time.'",time)';
        $appointments = DB::select($query);
        return response()->json([$appointments]);
    }

    public function store(Request $request)
    {
        $date = date("Y-m-d", strtotime($request->date));
        DB::beginTransaction();
        try {
            $appointment = Appointment::where(['name' => $request->name, 'phone' => $request->phone])->first();
            if($appointment === null ){
                Appointment::create([
                    'name'          => $request->name,
                    'phone'         => $request->phone,
                    'description'   => $request->description,
                    'time'          => implode(',', $request->time),
                    'date'          => $date
                ]);
            }else{
                return response()->json([
                    'message' => 'duplicate'
                ]);
                //$appointment->update(['time' => $appointment->time . $request->time . ',']);
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

    public function edit(Appointment $appointment)
    {
        return view('appointments.edit')->with(['appointment'  => $appointment ]);
    }

    public function update(Request $request, Appointment $appointment)
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
            $purchase_updated = Appointment::whereId($appointment->id)->update([
                'name'          => $request->name,
                'selling_price' => $request->selling_price,
                'net_price'     => $request->net_price,
                'qty'           => $request->qty,
                'created_time'   => $created_time
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('appointments.index')->with('success','Appointment Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function delete(Appointment $appointment)
    {
        DB::beginTransaction();
        try {
            // Delete Patient
            $appointment = Appointment::whereId($appointment->id)->delete();

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

}
