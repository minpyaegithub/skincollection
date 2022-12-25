<?php

namespace App\Http\Controllers;

use App\Models\Photo;
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

class PatientPhotoController extends Controller
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
        //$purchase = Purchase::all();
        $query = 'SELECT photos.id, photos.description, patient.token, patient.first_name, patient.last_name, DATE_FORMAT(photos.created_time,"%d-%m-%Y") AS created_time FROM photos photos LEFT JOIN patients patient on photos.patient_id = patient.id ORDER BY photos.created_time DESC';
        $photos = DB::select($query);
        return view('patient-photo.index', ['photos' => $photos]);
    }
    
    public function create()
    {
        $patients = Patient::all();
        return view('patient-photo.add', ['patients' => $patients]);
    }

    public function store(Request $request)
    {
        $created_time = date("Y-m-d", strtotime($request->created_time)); 
        // Validations
        $request->validate([
            'patient_id' => 'required',
            'created_time'     => 'required'
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

            $photo = Photo::create([
                'patient_id'    => $request->patient_id,
                'description'   => $request->description,
                'photo'         => json_encode($names),
                'created_time'  => $created_time
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('photo.create')->with('success','Patient Photo Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function edit(Photo $photo)
    {
        $patients = Patient::all();
        return view('patient-photo.edit')->with(['photo'  => $photo, 'patients' => $patients ]);
    }

    public function update(Request $request, Photo $photo)
    {
        $created_time = date("Y-m-d", strtotime($request->created_time)); 
        // Validations
        $request->validate([
            'patient_id' => 'required',
            'created_time'     => 'required'
        ]);

        DB::beginTransaction();
        try {

            $names = [];
            $preloaded = [];

            $old_img = Photo::whereId($photo->id)->get()->toArray();
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
            $photo_updated = Photo::whereId($photo->id)->update([
                'patient_id'    => $request->patient_id,
                'description'   => $request->description,
                'created_time'  => $created_time,
                'photo'         => json_encode($image_all)
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('photo.create')->with('success','Patient Photo Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function delete(Photo $photo)
    {
        DB::beginTransaction();
        try {
            // Delete Patient
            $old_img = Photo::whereId($photo->id)->get()->toArray();
            $old_img_arr = json_decode($old_img[0]['photo']);
            if($old_img_arr){
                foreach($old_img_arr as $img){
                    if(file_exists(public_path('patient-photo/'.$img))){
                        unlink(public_path('patient-photo/'.$img));
                    }
                    
                }
            }

            $photo = Photo::whereId($photo->id)->delete();

            DB::commit();
            return $photo;
            //return redirect()->route('patients.index')->with('success', 'Patient Deleted Successfully!.');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
