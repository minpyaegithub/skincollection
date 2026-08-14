<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPatientsJson extends Command
{
    protected $signature = 'import:patients-json
                            {file : Full path to the JSON or JSONL export file}
                            {--clinic-id=1 : Clinic ID to assign imported records}
                            {--dry-run : Preview counts without writing to the database}';

    protected $description = 'Import patients + photos from a JSON export file into a specific clinic';

    public function handle(): int
    {
        $file     = $this->argument('file');
        $clinicId = (int) $this->option('clinic-id');
        $dryRun   = (bool) $this->option('dry-run');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $clinic = DB::table('clinics')->find($clinicId);
        if (!$clinic) {
            $this->error("Clinic ID {$clinicId} does not exist.");
            DB::table('clinics')->get(['id', 'name'])
                ->each(fn($c) => $this->line("  [{$c->id}] {$c->name}"));
            return self::FAILURE;
        }

        $this->info('==============================================');
        $this->info('  Patients + Photos JSON Import');
        $this->info('==============================================');
        $this->info("File     : {$file}");
        $this->info("Clinic   : [{$clinic->id}] {$clinic->name}");
        $this->info("Dry run  : " . ($dryRun ? 'YES' : 'NO'));
        $this->newLine();

        ['patients' => $patients, 'photos' => $photos] = $this->parseFile($file);

        $this->info("Records found  ->  patients: " . count($patients) . "  |  photos: " . count($photos));
        $this->newLine();

        if (empty($patients)) {
            $this->warn('No patient records found in file.');
            return self::SUCCESS;
        }

        // token => existing new DB id
        $existingTokens = DB::table('patients')
            ->where('clinic_id', $clinicId)
            ->pluck('id', 'token')
            ->toArray();

        $patientRows   = [];
        $oldToNewId    = [];
        $skipped       = 0;
        $patientErrors = 0;

        $this->info('Processing patients ...');
        $bar = $this->output->createProgressBar(count($patients));
        $bar->start();

        foreach ($patients as $p) {
            $token = trim($p['token'] ?? '');
            $oldId = (int) ($p['id'] ?? 0);

            if (!$token) {
                $patientErrors++;
                $bar->advance();
                continue;
            }

            if (isset($existingTokens[$token])) {
                $oldToNewId[$oldId] = $existingTokens[$token];
                $skipped++;
                $bar->advance();
                continue;
            }

            $existingTokens[$token] = null;

            $patientRows[$oldId] = [
                'clinic_id'  => $clinicId,
                'first_name' => trim($p['first_name'] ?? ''),
                'last_name'  => isset($p['last_name'])  ? trim($p['last_name'])  : null,
                'gender'     => $p['gender']   ?? 'Female',
                'age'        => isset($p['age'])    && is_numeric($p['age'])    ? (int)   $p['age']    : null,
                'address'    => isset($p['address']) ? trim($p['address'])  : null,
                'phone'      => isset($p['phone'])   ? trim($p['phone'])    : null,
                'email'      => isset($p['email'])   ? trim($p['email'])    : null,
                'weight'     => isset($p['weight']) && is_numeric($p['weight']) ? (float) $p['weight'] : null,
                'feet'       => isset($p['feet'])   && is_numeric($p['feet'])   ? (int)   $p['feet']   : null,
                'inches'     => isset($p['inches']) && is_numeric($p['inches']) ? (int)   $p['inches'] : null,
                'BMI'        => isset($p['BMI'])    && is_numeric($p['BMI'])    ? (float) $p['BMI']    : null,
                'disease'    => isset($p['disease']) ? (trim($p['disease']) ?: null) : null,
                'photo'      => '[]',
                'token'      => $token,
                'created_at' => $p['created_at'] ?? now()->toDateTimeString(),
                'updated_at' => $p['updated_at'] ?? now()->toDateTimeString(),
            ];

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $insertedPatients = 0;

        if (!$dryRun && !empty($patientRows)) {
            DB::transaction(function () use ($patientRows, $clinicId, &$oldToNewId, &$insertedPatients) {
                foreach (array_chunk($patientRows, 200, true) as $chunk) {
                    $tokens = array_column(array_values($chunk), 'token');
                    DB::table('patients')->insert(array_values($chunk));
                    $insertedPatients += count($chunk);

                    $newIdMap = DB::table('patients')
                        ->where('clinic_id', $clinicId)
                        ->whereIn('token', $tokens)
                        ->pluck('id', 'token')
                        ->toArray();

                    foreach ($chunk as $oldId => $row) {
                        if (isset($newIdMap[$row['token']])) {
                            $oldToNewId[$oldId] = $newIdMap[$row['token']];
                        }
                    }
                }
            });
        } else {
            $fakeId = -1;
            foreach (array_keys($patientRows) as $oldId) {
                $oldToNewId[$oldId] = $fakeId--;
                $insertedPatients++;
            }
        }

        $photoRows      = [];
        $photoSkipped   = 0;
        $photoErrors    = 0;
        $insertedPhotos = 0;

        if (!empty($photos)) {
            $this->info('Processing photos ...');
            $bar2 = $this->output->createProgressBar(count($photos));
            $bar2->start();

            foreach ($photos as $ph) {
                $oldPatientId = (int) ($ph['patient_id'] ?? 0);

                if (!$oldPatientId || !isset($oldToNewId[$oldPatientId])) {
                    $photoErrors++;
                    $bar2->advance();
                    continue;
                }

                $newPatientId = $oldToNewId[$oldPatientId];

                if (!$dryRun && $newPatientId > 0) {
                    $snippet = mb_substr(trim($ph['description'] ?? ''), 0, 100);
                    $exists  = DB::table('photos')
                        ->where('patient_id', $newPatientId)
                        ->where('clinic_id',  $clinicId)
                        ->whereRaw('LEFT(description, 100) = ?', [$snippet])
                        ->exists();

                    if ($exists) {
                        $photoSkipped++;
                        $bar2->advance();
                        continue;
                    }
                }

                $photoRows[] = [
                    'clinic_id'     => $clinicId,
                    'patient_id'    => $newPatientId,
                    'description'   => $ph['description']  ?? null,
                    'created_time'  => $ph['created_time'] ?? null,
                    'photo'         => $ph['photo']        ?? '[]',
                    'filename'      => null,
                    'original_name' => null,
                    'file_path'     => null,
                    'file_type'     => null,
                    'file_size'     => null,
                    'created_at'    => $ph['created_at']   ?? now()->toDateTimeString(),
                    'updated_at'    => $ph['updated_at']   ?? now()->toDateTimeString(),
                ];

                $bar2->advance();
            }

            $bar2->finish();
            $this->newLine(2);

            if (!$dryRun && !empty($photoRows)) {
                DB::transaction(function () use ($photoRows, &$insertedPhotos) {
                    foreach (array_chunk($photoRows, 200) as $chunk) {
                        DB::table('photos')->insert($chunk);
                        $insertedPhotos += count($chunk);
                    }
                });
            } else {
                $insertedPhotos = count($photoRows);
            }
        }

        $this->table(
            ['Entity', 'Status', 'Count'],
            [
                ['Patients', 'Inserted',                   $insertedPatients],
                ['Patients', 'Skipped (duplicate token)',  $skipped],
                ['Patients', 'Errors (missing token)',     $patientErrors],
                ['Photos',   'Inserted',                   $insertedPhotos],
                ['Photos',   'Skipped (duplicate)',        $photoSkipped],
                ['Photos',   'Errors (no patient match)',  $photoErrors],
            ]
        );

        if ($dryRun) {
            $this->warn('DRY RUN -- no data was written to the database.');
        } else {
            $this->info("Import complete for clinic [{$clinicId}] {$clinic->name}.");
        }

        return self::SUCCESS;
    }

    /**
     * Parse the export file and return patients + photos arrays.
     *
     * Format A (multi-table wrapper):
     *   [{"type":"table","name":"patients","data":[...]},
     *    {"type":"table","name":"photos","data":[...]}, ...]
     *
     * Format B (plain JSON array):  [{...}, ...]
     * Format C (JSONL):             one object per line
     *
     * @return array{patients: array, photos: array}
     */
    private function parseFile(string $file): array
    {
        $content = file_get_contents($file);
        $trimmed = trim($content);

        $patients = [];
        $photos   = [];

        // Format A
        if (str_starts_with($trimmed, '[') && str_contains($trimmed, '"type":"table"')) {
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach ($decoded as $block) {
                    if (($block['type'] ?? '') !== 'table') continue;
                    if (($block['name'] ?? '') === 'patients') $patients = $block['data'] ?? [];
                    if (($block['name'] ?? '') === 'photos')   $photos   = $block['data'] ?? [];
                }
                return compact('patients', 'photos');
            }

            // Full decode failed — parse line-by-line
            $lines        = explode("\n", $trimmed);
            $currentTable = null;
            $dataStarted  = false;
            $buffer       = '';

            foreach ($lines as $raw) {
                $line     = ltrim($raw, ',');
                $stripped = trim($line);

                if (preg_match('/\{"type":"table","name":"(\w+)"/', $stripped, $m)) {
                    if ($currentTable !== null && $buffer !== '') {
                        $this->flushBuffer($currentTable, $buffer, $patients, $photos);
                    }
                    $currentTable = $m[1];
                    $dataStarted  = false;
                    $buffer       = '';
                    continue;
                }

                if ($currentTable === null) continue;

                if ($stripped === '[') {
                    $dataStarted = true;
                    continue;
                }

                if ($dataStarted && in_array(rtrim($stripped, "\r"), [']', '}', ']'], true)) {
                    $this->flushBuffer($currentTable, $buffer, $patients, $photos);
                    $currentTable = null;
                    $dataStarted  = false;
                    $buffer       = '';
                    continue;
                }

                if ($dataStarted && !empty($stripped)) {
                    $buffer .= $stripped . "\n";
                }
            }

            if ($currentTable !== null && $buffer !== '') {
                $this->flushBuffer($currentTable, $buffer, $patients, $photos);
            }

            return compact('patients', 'photos');
        }

        // Format B
        if (str_starts_with($trimmed, '[')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) $patients = $decoded;
            return compact('patients', 'photos');
        }

        // Format C: JSONL
        foreach (explode("\n", $trimmed) as $line) {
            $line = trim(rtrim(trim($line), ','));
            if (empty($line)) continue;
            $obj = json_decode($line, true);
            if (is_array($obj)) $patients[] = $obj;
        }

        return compact('patients', 'photos');
    }

    private function flushBuffer(string $table, string $buffer, array &$patients, array &$photos): void
    {
        $json = '[' . rtrim(trim($buffer), ',') . ']';
        $rows = json_decode($json, true);
        if (!is_array($rows)) return;
        if ($table === 'patients') $patients = array_merge($patients, $rows);
        if ($table === 'photos')   $photos   = array_merge($photos, $rows);
    }
}
