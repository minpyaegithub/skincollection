<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Services\S3Service;
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
        $query = 'SELECT photos.id, photos.description, patient.token, patient.first_name, patient.last_name, DATE_FORMAT(photos.created_at,"%d-%m-%Y") AS created_time FROM photos photos LEFT JOIN patients patient on photos.patient_id = patient.id ORDER BY DATE(photos.created_at) ASC';
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
        // Validations
        $request->validate([
            'patient_id' => 'required',
            // If the form still sends created_time, we accept it but do not persist it (schema uses timestamps)
            'created_time' => 'nullable',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp',
        ]);

         DB::beginTransaction();
        try {

            $disk = config('filesystems.patient_photos_disk', 's3');
            $visibility = config('filesystems.patient_photos_visibility', 'private');

            $patient = Patient::findOrFail($request->patient_id);
            $prefix = $patient?->clinic?->prefix ?? 'default';
            $createdAt = $request->created_time ? Carbon::parse($request->created_time) : now();

            $photoKeys = [];
            $lastFileMeta = [
                'filename' => null,
                'original_name' => null,
                'file_type' => null,
                'file_size' => null,
            ];

            if ($request->hasFile('images')) {
                foreach ((array) $request->file('images') as $image) {
                    if (!$image) {
                        continue;
                    }

                    $safeOriginal = Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME));
                    $ext = strtolower($image->getClientOriginalExtension() ?: 'jpg');
                    $name = now()->format('YmdHis') . '_' . Str::random(8) . '_' . ($safeOriginal ?: 'photo') . '.' . $ext;
                    $path = 'patient-photos/' . $prefix;

                    $storedPath = Storage::disk($disk)->putFileAs(
                        $path,
                        $image,
                        $name,
                        ['visibility' => $visibility]
                    );

                    if ($storedPath) {
                        $photoKeys[] = $storedPath;
                        $lastFileMeta = [
                            'filename' => $name,
                            'original_name' => $image->getClientOriginalName(),
                            'file_type' => $image->getClientMimeType() ?: $ext,
                            'file_size' => $image->getSize() ?: 0,
                        ];
                    }
                }
            }

            // One DB row per "photo record"; store many images in metadata.photos
            Photo::create([
                'clinic_id' => $patient?->clinic_id,
                'patient_id' => $patient->id,
                // Keep these columns filled for backward compatibility (use last uploaded file as representative)
                'filename' => $lastFileMeta['filename'] ?? 'photos',
                'original_name' => $lastFileMeta['original_name'] ?? 'photos',
                'file_path' => $photoKeys[0] ?? ($lastFileMeta['filename'] ?? 'photos'),
                'file_type' => $lastFileMeta['file_type'] ?? 'image/*',
                'file_size' => (int) ($lastFileMeta['file_size'] ?? 0),
                'description' => $request->description,
                'metadata' => [
                    'photos' => $photoKeys,
                ],
                'created_at' => $createdAt,
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

    public function view(Photo $photo)
    {
        $patient = Patient::findOrFail($photo->patient_id);

        $keys = (array) data_get($photo->metadata, 'photos', []);
        if (empty($keys) && !empty($photo->file_path)) {
            $keys = [$photo->file_path];
        }

        // Signed URLs (private bucket friendly)
        $photos = collect($keys)
            ->filter(fn ($k) => is_string($k) && trim($k) !== '')
            ->map(fn ($k) => [
                'key' => $k,
                'url' => S3Service::url($k),
            ])
            ->values();

        return view('patient-photo.view', compact('photo', 'patient', 'photos'));
    }

    public function update(Request $request, Photo $photo)
    {
        // Validations
        $request->validate([
            'patient_id' => 'required',
            'created_time' => 'nullable',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp',
        ]);

        DB::beginTransaction();
        try {

            $disk = config('filesystems.patient_photos_disk', 's3');
            $visibility = config('filesystems.patient_photos_visibility', 'private');

            // Photo module: ONE DB row per photo record, multiple images stored in metadata.photos

            $patient = Patient::findOrFail($request->patient_id);
            $prefix = $patient?->clinic?->prefix ?? 'default';
            $createdAt = $request->created_time ? Carbon::parse($request->created_time) : now();

            $keptKeys = array_values(array_filter(Arr::wrap($request->preloaded)));
            $keptKeys = array_values(array_filter($keptKeys, fn ($v) => is_string($v) && trim($v) !== ''));

            $existingKeys = (array) data_get($photo->metadata, 'photos', []);

            $newKeys = [];
            $lastFileMeta = [
                'filename' => $photo->filename,
                'original_name' => $photo->original_name,
                'file_type' => $photo->file_type,
                'file_size' => $photo->file_size,
            ];

            if ($request->hasFile('images')) {
                foreach ((array) $request->file('images') as $image) {
                    if (!$image) {
                        continue;
                    }

                    $safeOriginal = Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME));
                    $ext = strtolower($image->getClientOriginalExtension() ?: 'jpg');
                    $name = now()->format('YmdHis') . '_' . Str::random(8) . '_' . ($safeOriginal ?: 'photo') . '.' . $ext;
                    $path = 'patient-photos/' . $prefix;

                    $storedPath = Storage::disk($disk)->putFileAs(
                        $path,
                        $image,
                        $name,
                        ['visibility' => $visibility]
                    );

                    if ($storedPath) {
                        $newKeys[] = $storedPath;
                        $lastFileMeta = [
                            'filename' => $name,
                            'original_name' => $image->getClientOriginalName(),
                            'file_type' => $image->getClientMimeType() ?: $ext,
                            'file_size' => $image->getSize() ?: 0,
                        ];
                    }
                }
            }

            $finalKeys = array_values(array_unique(array_merge($keptKeys, $newKeys)));

            // delete removed keys from S3
            $removed = array_diff($existingKeys, $finalKeys);
            foreach ($removed as $key) {
                if (is_string($key) && trim($key) !== '') {
                    Storage::disk($disk)->delete($key);
                }
            }

            $photo->patient_id = $patient->id;
            $photo->clinic_id = $patient?->clinic_id;
            $photo->description = $request->description;
            $photo->metadata = array_merge((array) ($photo->metadata ?? []), ['photos' => $finalKeys]);
            $photo->created_at = $createdAt;

            // keep compatibility columns in sync
            $photo->filename = $lastFileMeta['filename'] ?? $photo->filename;
            $photo->original_name = $lastFileMeta['original_name'] ?? $photo->original_name;
            $photo->file_type = $lastFileMeta['file_type'] ?? $photo->file_type;
            $photo->file_size = (int) ($lastFileMeta['file_size'] ?? $photo->file_size);
            $photo->file_path = $finalKeys[0] ?? $photo->file_path;

            $photo->save();

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
            $disk = config('filesystems.patient_photos_disk', 's3');
            $keys = (array) data_get($photo->metadata, 'photos', []);
            foreach ($keys as $k) {
                if (is_string($k) && trim($k) !== '') {
                    Storage::disk($disk)->delete($k);
                }
            }

            // fallback to single file_path if older rows exist
            if ($photo->file_path) {
                Storage::disk($disk)->delete($photo->file_path);
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
