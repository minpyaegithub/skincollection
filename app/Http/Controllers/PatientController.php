<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Exports\PatientsExport;
use App\Services\ClinicContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
        $user = auth()->user();

        $query = Patient::with('clinic')->orderBy('created_at', 'desc');

        // Default behaviour:
        // - Admin/Doctor: show all patients (no filter applied by default).
        // - Other roles: only show patients from the user's clinic.
        $canSeeAllPatients = $user && ($user->isAdmin() || $user->isDoctor());

        if (!$canSeeAllPatients) {
            $query->where('clinic_id', $user?->clinic_id);
        } else {
            // Only Admin can optionally filter by clinic via query string.
            // If not provided, show all.
            if ($user->isAdmin()) {
                $clinicId = request()->get('clinic_id');
                if ($clinicId && $clinicId !== 'all') {
                    $query->where('clinic_id', (int) $clinicId);
                }
            }
        }

        $patients = $query->get();

        return view('patients.index', [
            'patients' => $patients,
            // Middleware shares availableClinics/viewingAllClinics, but we also pass this to keep the view robust.
            'availableClinics' => $user ? app(ClinicContext::class)->availableClinics($user) : collect(),
            'selectedClinicId' => request()->get('clinic_id', 'all'),
        ]);
    }
    
    public function create()
    {
       
        return view('patients.add');
    }

    public function store(Request $request)
    {
        //$token = rand(000000,999999);
        // $created_time = null;
        // if($request->dob)
        //     $created_time = date("Y-m-d", strtotime($request->dob)); 

        // Get clinic from authenticated user
        $clinic = auth()->user()->clinic ?? \App\Models\Clinic::first();
        
        if (!$clinic) {
            return redirect()->back()->with('error', 'No clinic found. Please contact administrator.');
        }

        // Validations
        $request->validate([
            'first_name'    => 'required',
            'last_name'     => 'required',
            //'email'         => 'required|unique:users,email',
            //'phone' => 'required|numeric',
            'gender'    => 'required',
            //'dob'     => 'required',
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
                    $filename = 'patient-photos/' . $clinic->prefix . '/' . time().'_'.rand(1,99).'_'.$image->getClientOriginalName();
                    Storage::disk('s3')->put($filename, file_get_contents($image));
                    array_push($names, $filename);          

                }
            }

            // Generate token inside the transaction to avoid race conditions.
            // If another request generated the same token first, retry a few times.
            $attempts = 0;
            $maxAttempts = 5;
            $patient = null;

            while ($attempts < $maxAttempts && $patient === null) {
                $attempts++;
                $token = $this->patientId($clinic);

                try {
                    $patient = Patient::create([
                        'first_name'    => $request->first_name,
                        'last_name'     => $request->last_name,
                        'email'         => $request->email,
                        'phone'         => $request->phone,
                        'gender'        => $request->gender,
                        'age'           => $request->age,
                        'address'       => $request->address,
                        'weight'        => $request->weight,
                        'feet'          => $request->feet,
                        'inches'        => $request->inches,
                        'BMI'           => $request->bmi,
                        'disease'       => $request->disease,
                        'photo'         => json_encode($names),
                        'token'         => $token,
                        // Ensure the clinic is persisted so patientId() can correctly increment per-clinic.
                        'clinic_id'     => $clinic->id,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // MySQL duplicate key error (SQLSTATE 23000 / error code 1062)
                    $errorCode = (int) ($e->errorInfo[1] ?? 0);
                    if ($errorCode === 1062) {
                        // Collision - retry.
                        $patient = null;
                        continue;
                    }

                    throw $e;
                }
            }

            if ($patient === null) {
                throw new \RuntimeException('Could not generate a unique patient token. Please try again.');
            }

            

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('patients.index')->with('success','Patient Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    function patientId(\App\Models\Clinic $clinic)
    {
        // Use withoutGlobalScopes to get the absolute latest patient for the clinic
        $latest = Patient::withoutGlobalScopes()->where('clinic_id', $clinic->id)->latest('id')->first();
        if (! $latest) {
            return $clinic->prefix . '0001';
        }

        $string = preg_replace("/[^0-9\.]/", '', $latest->token);

        return $clinic->prefix . sprintf('%04d', $string + 1);
    }

    public function edit(Patient $patient)
    {
        $photoKeys = [];
        try {
            $photoKeys = $patient->photo ? json_decode($patient->photo, true) : [];
        } catch (\Throwable $e) {
            $photoKeys = [];
        }

        if (!is_array($photoKeys)) {
            $photoKeys = [];
        }

        // Preload signed URLs for private S3 buckets so the edit form can display images.
        $preloadedPhotos = collect($photoKeys)
            ->filter(fn ($key) => is_string($key) && trim($key) !== '')
            ->map(fn ($key) => [
                'id' => $key,
                'src' => \App\Services\S3Service::url($key),
            ])
            ->values();

        return view('patients.edit')->with([
            'patient' => $patient,
            'preloadedPhotos' => $preloadedPhotos,
        ]);
    }

    public function profile(Patient $patient)
    {
        // Eager load relationships for efficiency
        $patient->load(['weights', 'invoices', 'photos', 'records']);

        $patient_weight = $patient->weights()
            ->select(DB::raw('DATE(created_time) as date'), 'weight')
            ->whereYear('created_time', today()->year)
            ->whereMonth('created_time', today()->month)
            ->distinct()
            ->orderBy('date', 'asc')
            ->get();

        $invoices = $patient->invoices()
            ->with(['clinic', 'items.treatment', 'items.pharmacy'])
            ->orderByDesc('invoice_date')
            ->orderByDesc('created_at')
            ->get();

        $photos = $patient->photos()->orderBy('created_time', 'desc')->get()->groupBy(function($item) {
            return $item->created_time->format('d M Y');
        });

        $weights = $patient->weights()->orderBy('created_time', 'desc')->get();

        $records = $patient->records()->orderBy('created_time', 'asc')->get();

        return view('patients.profile')->with(['patient'  => $patient, 'patient_weight'=> $patient_weight, 'invoices'=>$invoices, 'photos'=>$photos, 'records'=>$records, 'weights'=>$weights ]);
    }

    public function update(Request $request, Patient $patient)
    {
        // $created_time = null;
        // if($request->dob)
        //     $created_time = date("Y-m-d", strtotime($request->dob)); 
        // Validations
        $request->validate([
            'first_name'    => 'required',
            'last_name'     => 'required',
            //'email'         => 'required|unique:users,email',
            //'phone' => 'required|numeric',
            'gender'    => 'required',
            //'dob'     => 'required',
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
                // imageUploader sends preloaded values as array; keep only non-empty strings.
                $preloaded = array_values(array_filter((array) $request->preloaded));
            }

            if($request->images)
            {  
                foreach($request->images as $image)
                {
                    $filename = time().'_'.rand(1,99).'_'.$image->getClientOriginalName();
                    $path = 'patient-photos/' . $patient->clinic->prefix . '/' . $filename;
                    Storage::disk('s3')->put($path, file_get_contents($image));
                    // Persist the full S3 key so rendering/deletion is consistent.
                    $names[] = $path;

                }
            }
            
            $image_all = array_merge($names, $preloaded);

            if($old_img_arr){
                foreach($old_img_arr as $img){
                    if (!$img) continue;
                    if (!in_array($img, $image_all)){
                        Storage::disk('s3')->delete($img);
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
                //'dob'           => $created_time,
                'age'           => $request->age,
                'address'       => $request->address,
                'weight'        => $request->weight,
                'feet'          => $request->feet,
                'inches'        => $request->inches,
                'BMI'           => $request->bmi,
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
                    Storage::disk('s3')->delete($img);
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
