<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3Service
{
    /**
     * Upload a file to S3
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string
     */
    public static function upload(UploadedFile $file, string $folder = 'uploads'): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;
        
        Storage::disk('s3')->put($path, file_get_contents($file));
        
        return $path;
    }

    /**
     * Upload multiple files to S3
     *
     * @param array $files
     * @param string $folder
     * @return array
     */
    public static function uploadMultiple(array $files, string $folder = 'uploads'): array
    {
        $uploadedFiles = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploadedFiles[] = self::upload($file, $folder);
            }
        }
        
        return $uploadedFiles;
    }

    /**
     * Delete a file from S3
     *
     * @param string $path
     * @return bool
     */
    public static function delete(string $path): bool
    {
        return Storage::disk('s3')->delete($path);
    }

    /**
     * Get the URL for a file on S3
     *
     * @param string $path
     * @return string
     */
    public static function url(string $path): string
    {
        $disk = config('filesystems.patient_photos_disk', 's3');

        if (config('filesystems.patient_photos_visibility', 'private') === 'public') {
            return Storage::disk($disk)->url($path);
        }

        // Private buckets: return a short-lived signed URL.
        return Storage::disk($disk)->temporaryUrl($path, now()->addMinutes(15));
    }

    /**
     * Upload patient photos to S3
     *
     * @param array $photos
     * @param int $clinicId
     * @return array
     */
    public static function uploadPatientPhotos(array $photos, int $clinicId): array
    {
        $folder = "patient-photos/clinic-{$clinicId}";
        return self::uploadMultiple($photos, $folder);
    }
}
