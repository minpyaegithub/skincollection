<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Patient;
use App\Services\S3Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Yajra\Datatables\Datatables;
use Input;

class PatientRecordController extends Controller
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
        $query = 'SELECT record.id, patient.token, patient.first_name, patient.last_name, record.title, record.description, DATE_FORMAT(record.record_date,"%d-%m-%Y") AS created_time FROM records record LEFT JOIN patients patient on record.patient_id = patient.id ORDER BY record.record_date DESC';
        $records = DB::select($query);
        return view('patient-record.index', ['records' => $records]);
    }
    
    public function create()
    {
        $patients = Patient::all();
        return view('patient-record.add', ['patients' => $patients]);
    }

    public function store(Request $request)
    {
        $record_date = date("Y-m-d", strtotime($request->created_time));
        // Validations
        $request->validate([
            'patient_id' => 'required',
            'created_time'     => 'required',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp',
        ]);

         DB::beginTransaction();
        try {

            // Upload images to S3 (private) and store keys in metadata
            $photoKeys = [];
            if ($request->hasFile('images')) {
                $patient = Patient::find($request->patient_id);
                $clinicId = $patient?->clinic_id ?? 0;
                $photoKeys = S3Service::uploadPatientPhotos($request->file('images'), (int) $clinicId);
            }

            $metadata = [
                'photos' => $photoKeys,
            ];

            $record = Record::create([
                'patient_id'       => $request->patient_id,
                'title' => $request->title ?? 'Record',
                'description' => $request->description,
                'record_date'  => $record_date,
                'metadata' => $metadata,
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('record.index')->with('success','Patient Record Created Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function edit(Record $record)
    {
        $patients = Patient::all();

        $photoKeys = (array) data_get($record->metadata, 'photos', []);
        $preloadedPhotos = collect($photoKeys)
            ->filter(fn ($key) => is_string($key) && trim($key) !== '')
            ->map(fn ($key) => [
                'id' => $key,
                'src' => S3Service::url($key),
            ])
            ->values();

        return view('patient-record.edit')->with([
            'record'  => $record,
            'patients' => $patients,
            'preloadedPhotos' => $preloadedPhotos,
        ]);
    }

    public function view(Record $record)
    {
        $patient = Patient::findOrFail($record->patient_id);

        $metadata = is_array($record->metadata) ? $record->metadata : [];
        $photoKeys = [];
        if (isset($metadata['photos']) && is_array($metadata['photos'])) {
            $photoKeys = array_values(array_filter($metadata['photos'], fn ($v) => is_string($v) && $v !== ''));
        }

        $photos = collect($photoKeys)
            ->map(fn ($key) => ['key' => $key, 'url' => S3Service::url($key)])
            ->values();

        return view('patient-record.view', compact('record', 'patient', 'photos'));
    }

    public function update(Request $request, Record $record)
    {
        $record_date = date("Y-m-d", strtotime($request->created_time));
        // Validations
        $request->validate([
            'patient_id' => 'required',
            'created_time'     => 'required',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp',
        ]);

        DB::beginTransaction();
        try {

            $existingKeys = (array) data_get($record->metadata, 'photos', []);
            $keptKeys = [];
            if ($request->preloaded) {
                $keptKeys = array_values(array_filter((array) $request->preloaded));
            }

            // Upload new images
            $newKeys = [];
            if ($request->hasFile('images')) {
                $patient = Patient::find($request->patient_id);
                $clinicId = $patient?->clinic_id ?? 0;
                $newKeys = S3Service::uploadPatientPhotos($request->file('images'), (int) $clinicId);
            }

            $finalKeys = array_values(array_unique(array_merge($keptKeys, $newKeys)));

            // Delete removed photos from S3
            $removed = array_diff($existingKeys, $finalKeys);
            foreach ($removed as $key) {
                if (is_string($key) && trim($key) !== '') {
                    S3Service::delete($key);
                }
            }

            $metadata = (array) ($record->metadata ?? []);
            $metadata['photos'] = $finalKeys;

            // Store Data
            $record_updated = Record::whereId($record->id)->update([
                'patient_id'       => $request->patient_id,
                'title' => $request->title ?? ($record->title ?? 'Record'),
                'description' => $request->description,
                'record_date'  => $record_date,
                'metadata' => $metadata,
            ]);

            // Commit And Redirected To Listing
            DB::commit();
            return redirect()->route('record.index')->with('success','Record Updated Successfully.');

        } catch (\Throwable $th) {
            // Rollback and return with Error
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }

    public function delete(Record $record)
    {
        DB::beginTransaction();
        try {
            // Delete Patient
            $record = Record::whereId($record->id)->delete();

            DB::commit();
            return $record;
            //return redirect()->route('patients.index')->with('success', 'Patient Deleted Successfully!.');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
