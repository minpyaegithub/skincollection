<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3Service
{
    public static function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk(config('filesystems.patient_photos_disk', 'public'));
    }

    /**
     * Upload a file to the configured storage disk
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string
     */
    public static function upload(UploadedFile $file, string $folder = 'uploads'): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;

        static::disk()->put($path, file_get_contents($file));

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
     * Delete a file from the configured storage disk
     *
     * @param string $path
     * @return bool
     */
    public static function delete(string $path): bool
    {
        return static::disk()->delete($path);
    }

    /**
     * Get the URL for a file on the configured storage disk
     *
     * @param string $path
     * @return string
     */
    public static function url(string $path): string
    {
        $disk = config('filesystems.patient_photos_disk', 'public');

        if (config('filesystems.patient_photos_visibility', 'public') === 'public') {
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
