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
        $appointments = Appointment::all();
        return view('appointment.index', ['appointments' => $appointments]);
    }
    
    public function create()
    {
        $appointments = Appointment::with('timeSlots')->whereDate('date', Carbon::today())->get();
        $times = [];
        foreach($appointments as $appointment){
            foreach($appointment->timeSlots as $timeSlot){
                array_push($times, $timeSlot->time);
            }
        }
        $filter_time = array_filter($times, 'strlen');
        $unique_time = array_unique($filter_time);
        $appointment_times = AppointmentTime::all();
        return view('appointment.add', ['booking_times' => $unique_time,'appointments'=>$appointments, 'appointment_times'=> $appointment_times]);
    }

    public function list(Request $request)
    {
        $date = date("Y-m-d", strtotime($request->date)); 
        $appointments = Appointment::with('timeSlots')->whereDate('date', $date)->get();
        $times = [];
        foreach($appointments as $appointment){
            foreach($appointment->timeSlots as $timeSlot){
                array_push($times, $timeSlot->time);
            }
        }
        
        $unique_time = array_unique($times);
        //$filter_time = array_filter($times, 'strlen');
        //dd($unique_time);
        return response()->json([json_encode($unique_time),$appointments]);
    }

    public function view(Request $request)
    {
        $date = date("Y-m-d", strtotime($request->date)); 
        $time = $request->time;
        $query = 'select * from appointments where DATE(date) = "'.$date.'" and FIND_IN_SET("'.$time.'",time)';
        $appointments = DB::select($query);
        return response()->json([$appointments]);
    }

    public function editAppointment(Request $request)
    {
        $id = $request->id; 
        $query = 'select * from appointments where id = "'.$id.'"';
        $appointments = DB::select($query);
        return response()->json([$appointments]);
    }

    public function store(Request $request)
    {
        $date = date("Y-m-d", strtotime($request->date));
        DB::beginTransaction();
        try {
            $appointment = Appointment::create([
                'name'          => $request->name,
                'phone'         => $request->phone,
                'description'   => $request->description,
                'status'        => $request->status,
                'time'          => implode(',', $request->time), // Keep for backward compatibility
                'date'          => $date
            ]);

            // Sync the many-to-many relationship
            if ($request->has('time') && is_array($request->time)) {
                $appointment->timeSlots()->sync($request->time);
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
        $appointment_times = AppointmentTime::all();
        return view('appointment.edit')->with(['appointment'  => $appointment, 'appointment_times' => $appointment_times ]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        // Validations
        $request->validate([
            'name'    => 'required',
            'phone' => 'required|numeric'
        ]);

        DB::beginTransaction();
        try {

            // Store Data
            $appointment_updated = Appointment::whereId($appointment->id)->update([
                'name'          => $request->name,
                'phone'         => $request->phone,
                'description'   => $request->description,
                'status'        => $request->status,
                'time'          => implode(',', $request->time)
            ]);

            // Sync the many-to-many relationship
            if ($request->has('time') && is_array($request->time)) {
                $appointment->timeSlots()->sync($request->time);
            }

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('appointments.create')->with('success','Appointment Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function updateAppointment(Request $request)
    {
        // Validations

        DB::beginTransaction();
        try {

            // Store Data
            $appointment = Appointment::findOrFail($request->id);
            $appointment_updated = $appointment->update([
                'name'          => $request->name,
                'phone'         => $request->phone,
                'description'   => $request->description,
                'status'        => $request->status,
                'time'          => implode(',', $request->time)
            ]);

            // Sync the many-to-many relationship
            if ($request->has('time') && is_array($request->time)) {
                $appointment->timeSlots()->sync($request->time);
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
