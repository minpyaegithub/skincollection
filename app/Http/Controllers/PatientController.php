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

class PatientController extends Controller
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
        $patients = Patient::orderBy('created_at','desc')->get();
        //dd(DB::getQueryLog());
        //return datatables($patients)->toJson();
        return view('patients.index', ['patients' => $patients]);
    }
    
    public function create()
    {
       
        return view('patients.add');
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
            //'phone' => 'required|numeric',
            'gender'    => 'required',
            'dob'     => 'required',
            //'address'    => 'required',
            //'weight'     => 'required',
           // 'feet'     => 'required',
            //'inches'    => 'required',
           // 'photo.*' => 'image|mimes:jpeg,png,jpg,gif,svg'
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
                    $filename = time().'_'.rand(1,99).'_'.$image->getClientOriginalName();
                    $image->move(public_path('patient-photo'), $filename);
                    array_push($names, $filename);          

                }
            }

            $patient = Patient::create([
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
            return redirect()->route('patients.index')->with('success','Patient Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit')->with(['patient'  => $patient ]);
    }

    public function profile(Patient $patient)
    {
        $patient_weight_query = 'select DISTINCT DATE(created_time) date,weight FROM weights WHERE patient_id="'.$patient->id.'" and MONTH(created_time) = MONTH(CURRENT_DATE()) AND YEAR(created_time) = YEAR(CURRENT_DATE()) GROUP BY DATE(created_time) ORDER BY created_time asc';
        $patient_weight = DB::select($patient_weight_query);

        $query = 'select id,count(*) as count,invoice_no,price,SUM(sub_total) total,type, DATE_FORMAT(created_time, "%d %M %Y") created_time FROM invoices WHERE patient_id="'.$patient->id.'" and type="treatment" GROUP BY invoice_no ORDER BY created_time asc ';
        $invoices = DB::select($query);

        $photo_query = 'select photo.id, photo.patient_id, photo.photo, DATE_FORMAT(photo.created_time, "%d %M %Y") created_time FROM photos photo WHERE photo.patient_id="'.$patient->id.'" GROUP BY photo.created_time ORDER BY photo.created_time desc ';
        $photos = DB::select($photo_query);

        $record_query = 'select id, description, DATE_FORMAT(created_time, "%d %M %Y") created_time FROM records WHERE patient_id="'.$patient->id.'"  ORDER BY created_time desc ';
        $records = DB::select($record_query);
        //dd($photos);

        return view('patients.profile')->with(['patient'  => $patient, 'patient_weight'=> $patient_weight, 'invoices'=>$invoices, 'photos'=>$photos, 'records'=>$records ]);
    }

    public function update(Request $request, Patient $patient)
    {
        $created_time = date("Y-m-d", strtotime($request->dob));
        // Validations
        $request->validate([
            'first_name'    => 'required',
            'last_name'     => 'required',
            //'email'         => 'required|unique:users,email',
            //'phone' => 'required|numeric',
            'gender'    => 'required',
            'dob'     => 'required',
           // 'address'    => 'required',
           // 'weight'     => 'required',
           // 'feet'     => 'required',
           // 'inches'    => 'required',
           // 'photo.*' => 'image|mimes:jpeg,png,jpg,gif,svg'
            //'disease'   => 'required'
        ]);

        DB::beginTransaction();
        try {

            $names = [];
            $preloaded = [];

            $old_img = Patient::whereId($patient->id)->get()->toArray();
            $old_img_arr = json_decode($old_img[0]['photo']);
            

            if($request->preloaded){
                $preloaded = $request->preloaded;
            }

            if($request->images)
            {  
                foreach($request->images as $image)
                {
                    $filename = time().'_'.rand(1,99).'_'.$image->getClientOriginalName();
                    $image->move(public_path('patient-photo'), $filename);
                    //$image->storeAs('images', $filename);
                    array_push($names, $filename);          

                }
            }
            
            $image_all = array_merge($names, $preloaded);

            if($old_img_arr){
                foreach($old_img_arr as $img){
                    if (!in_array($img, $image_all)){
                        if(file_exists(public_path('patient-photo/'.$img))){
                            unlink(public_path('patient-photo/'.$img));
                        }
                    }
                    
                }
            }
            
            // Store Data
            $patient_updated = Patient::whereId($patient->id)->update([
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
            return redirect()->route('patients.index')->with('success','Patient Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function delete(Patient $patient)
    {
        DB::beginTransaction();
        try {
            // Delete Patient
            $old_img = Patient::whereId($patient->id)->get()->toArray();
            $old_img_arr = json_decode($old_img[0]['photo']);
            if($old_img_arr){
                foreach($old_img_arr as $img){
                    if(file_exists(public_path('patient-photo/'.$img))){
                        unlink(public_path('patient-photo/'.$img));
                    }
                    
                }
            }
            $patient = Patient::whereId($patient->id)->delete();

            DB::commit();
            return $patient;
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
    public function importPatients()
    {
        return view('patients.import');
    }

    public function uploadUsers(Request $request)
    {
        Excel::import(new UsersImport, $request->file);
        
        return redirect()->route('users.index')->with('success', 'User Imported Successfully');
    }

    public function export() 
    {
        return Excel::download(new PatientsExport, 'patients.xlsx');
    }

}
