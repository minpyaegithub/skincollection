<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * ImportOldDatabase
 *
 * Imports data from the OLD skincollection database (single-clinic, no clinic_id)
 * into the NEW multi-clinic structure.
 *
 * OLD  →  NEW mapping:
 *  patients        → patients          (add clinic_id)
 *  appointments    → appointments      (add clinic_id)
 *  photos          → photos            (add clinic_id, flatten photo JSON → individual rows)
 *  pharmacies      → pharmacies        (add clinic_id)
 *  out_of_stocks   → out_of_stocks     (add clinic_id)
 *  purchases       → purchases         (unchanged except clinic_id via pharmacy)
 *  weights         → weights           (reshape many columns → weight + metadata JSON)
 *  records         → records           (add clinic_id, map created_time → record_date)
 *  treatments      → treatments        (add clinic_id, add duration_minutes/is_active)
 *  invoices (old)  → invoices + invoice_items  (completely restructured)
 *  sales (old)     → sales             (completely restructured)
 *  users           → users             (add clinic_id)
 *  roles/perms     → roles/perms       (direct copy)
 */
class ImportOldDatabase extends Command
{
    protected $signature = 'import:old-db
                            {--old-db=skincollection_old : Name of the OLD database on the same MySQL server}
                            {--clinic-id=1 : The clinic ID to assign all imported records to}
                            {--force : Skip confirmation prompt}
                            {--dry-run : Show what would be imported without writing anything}';

    protected $description = 'Import data from the old single-clinic database into the new multi-clinic structure';

    private int $clinicId;
    private string $oldDb;
    private bool $dryRun;
    private array $stats = [];

    public function handle(): int
    {
        $this->clinicId = (int) $this->option('clinic-id');
        $this->oldDb    = $this->option('old-db');
        $this->dryRun   = (bool) $this->option('dry-run');

        $this->info('==============================================');
        $this->info('  Old DB → New DB Import Tool');
        $this->info('==============================================');
        $this->info("Old database : {$this->oldDb}");
        $this->info("Target clinic: {$this->clinicId}");
        $this->info("Dry run      : " . ($this->dryRun ? 'YES (no writes)' : 'NO'));
        $this->newLine();

        // Verify old DB is accessible
        if (!$this->verifyOldDatabase()) {
            return self::FAILURE;
        }

        // Verify target clinic exists
        $clinic = DB::table('clinics')->find($this->clinicId);
        if (!$clinic) {
            $this->error("Clinic ID {$this->clinicId} does not exist in the new database.");
            $this->error("Please run migrations and seeders first, or use --clinic-id=<id>");
            return self::FAILURE;
        }
        $this->info("Target clinic: [{$clinic->id}] {$clinic->name}");
        $this->newLine();

        if (!$this->dryRun && !$this->option('force')) {
            if (!$this->confirm('This will INSERT data into the new database. Continue?')) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        }

        DB::transaction(function () {
            $this->importRolesAndPermissions();
            $this->importUsers();
            $this->importPatients();
            $this->importAppointmentTimes();
            $this->importAppointments();
            $this->importPharmacies();
            $this->importOutOfStocks();
            $this->importPurchases();
            $this->importTreatments();
            $this->importPhotos();
            $this->importWeights();
            $this->importRecords();
            $this->importInvoices();
            $this->importSales();
        });

        $this->newLine();
        $this->info('==============================================');
        $this->info('  Import Summary');
        $this->info('==============================================');
        foreach ($this->stats as $table => $count) {
            $this->line(sprintf('  %-25s %d rows', $table, $count));
        }
        $this->newLine();

        if ($this->dryRun) {
            $this->warn('DRY RUN — no data was written.');
        } else {
            $this->info('Import completed successfully!');
        }

        return self::SUCCESS;
    }

    // ------------------------------------------------------------------
    //  Helpers
    // ------------------------------------------------------------------

    private function old(string $table): \Illuminate\Database\Query\Builder
    {
        return DB::connection('mysql')->table("{$this->oldDb}.{$table}");
    }

    private function verifyOldDatabase(): bool
    {
        try {
            $exists = DB::select("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?", [$this->oldDb]);
            if (empty($exists)) {
                $this->error("Old database '{$this->oldDb}' not found.");
                $this->error("Create it first by importing the SQL dump:");
                $this->error("  mysql -u root -p < skincollection-structure-data.sql");
                $this->error("Then rename/set it with --old-db=<name>");
                return false;
            }
            // Quick table check
            DB::select("SELECT 1 FROM {$this->oldDb}.patients LIMIT 1");
            $this->info("✓ Old database '{$this->oldDb}' accessible.");
            return true;
        } catch (\Exception $e) {
            $this->error("Cannot access old database: " . $e->getMessage());
            return false;
        }
    }

    private function insert(string $table, array $rows): void
    {
        $this->stats[$table] = ($this->stats[$table] ?? 0) + count($rows);
        if ($this->dryRun || empty($rows)) {
            return;
        }
        // Insert in chunks to avoid max_packet issues
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table($table)->insertOrIgnore($chunk);
        }
    }

    // ------------------------------------------------------------------
    //  Roles & Permissions (direct copy — same Spatie structure)
    // ------------------------------------------------------------------

    private function importRolesAndPermissions(): void
    {
        $this->info('→ Roles & permissions...');

        // Only import if tables are empty to avoid duplicates
        if (DB::table('roles')->count() === 0) {
            $roles = $this->old('roles')->get()->map(fn($r) => (array) $r)->toArray();
            $this->insert('roles', $roles);
        } else {
            $this->line('  Roles already seeded — skipping.');
        }

        if (DB::table('permissions')->count() === 0) {
            $perms = $this->old('permissions')->get()->map(fn($r) => (array) $r)->toArray();
            $this->insert('permissions', $perms);
            $rp = $this->old('role_has_permissions')->get()->map(fn($r) => (array) $r)->toArray();
            $this->insert('role_has_permissions', $rp);
        } else {
            $this->line('  Permissions already seeded — skipping.');
        }
    }

    // ------------------------------------------------------------------
    //  Users  (add clinic_id)
    // ------------------------------------------------------------------

    private function importUsers(): void
    {
        $this->info('→ Users...');

        $existingEmails = DB::table('users')->pluck('email')->flip()->toArray();

        $rows = $this->old('users')->get()->map(function ($u) use ($existingEmails) {
            if (isset($existingEmails[$u->email])) {
                return null; // skip duplicates
            }
            return [
                'id'               => $u->id,
                'clinic_id'        => $this->clinicId,
                'first_name'       => $u->first_name,
                'last_name'        => $u->last_name,
                'email'            => $u->email,
                'mobile_number'    => $u->mobile_number ?? null,
                'email_verified_at'=> $u->email_verified_at ?? null,
                'password'         => $u->password,
                'role_id'          => $u->role_id,
                'status'           => $u->status,
                'remember_token'   => $u->remember_token ?? null,
                'created_at'       => $u->created_at,
                'updated_at'       => $u->updated_at,
            ];
        })->filter()->values()->toArray();

        $this->insert('users', $rows);

        // Restore model_has_roles
        if (!$this->dryRun) {
            $mr = $this->old('model_has_roles')->get()->map(fn($r) => (array) $r)->toArray();
            foreach (array_chunk($mr, 200) as $chunk) {
                DB::table('model_has_roles')->insertOrIgnore($chunk);
            }
        }
    }

    // ------------------------------------------------------------------
    //  Patients  (add clinic_id, old has `dob` instead of nothing new)
    // ------------------------------------------------------------------

    private function importPatients(): void
    {
        $this->info('→ Patients...');

        $existingTokens = DB::table('patients')->pluck('token')->flip()->toArray();

        $rows = $this->old('patients')->get()->map(function ($p) use ($existingTokens) {
            if (isset($existingTokens[$p->token])) {
                return null;
            }
            return [
                'id'         => $p->id,
                'clinic_id'  => $this->clinicId,
                'first_name' => $p->first_name,
                'last_name'  => $p->last_name ?? null,
                'email'      => $p->email ?? null,
                'phone'      => $p->phone ?? null,
                'gender'     => $p->gender,
                'age'        => $p->age ?? null,
                'address'    => $p->address ?? null,
                'weight'     => $p->weight ?? null,
                'feet'       => $p->feet ?? null,
                'inches'     => $p->inches ?? null,
                'BMI'        => $p->BMI ?? null,
                'disease'    => $p->disease ?? null,
                'photo'      => $p->photo ?? '[]',
                'token'      => $p->token,
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at,
            ];
        })->filter()->values()->toArray();

        $this->insert('patients', $rows);
    }

    // ------------------------------------------------------------------
    //  Appointments  (add clinic_id)
    // ------------------------------------------------------------------

    private function importAppointmentTimes(): void
    {
        $this->info('→ Appointment times...');

        if (DB::table('appointment_times')->count() > 0) {
            $this->line('  Appointment times already seeded — skipping.');
            return;
        }

        $rows = $this->old('appointment_times')->get()->map(fn($t) => [
            'id'          => $t->id,
            'time'        => $t->time,
            'custom_time' => $t->custom_time,
            'created_at'  => now(),
            'updated_at'  => now(),
        ])->toArray();

        $this->insert('appointment_times', $rows);
    }

    private function importAppointments(): void
    {
        $this->info('→ Appointments...');

        $rows = $this->old('appointments')->get()->map(fn($a) => [
            'id'          => $a->id,
            'clinic_id'   => $this->clinicId,
            'name'        => $a->name,
            'phone'       => $a->phone,
            'description' => $a->description ?? null,
            'status'      => $a->status ?? 0,
            'date'        => $a->date,
            'time'        => $a->time ?? null,
            'created_at'  => $a->created_at,
            'updated_at'  => $a->updated_at,
        ])->toArray();

        $this->insert('appointments', $rows);
    }

    // ------------------------------------------------------------------
    //  Pharmacies  (add clinic_id)
    // ------------------------------------------------------------------

    private function importPharmacies(): void
    {
        $this->info('→ Pharmacies...');

        $existingNames = DB::table('pharmacies')
            ->where('clinic_id', $this->clinicId)
            ->pluck('name')->flip()->toArray();

        $rows = $this->old('pharmacies')->get()->map(function ($p) use ($existingNames) {
            if (isset($existingNames[$p->name])) {
                return null;
            }
            return [
                'id'            => $p->id,
                'clinic_id'     => $this->clinicId,
                'name'          => $p->name,
                'selling_price' => $p->selling_price,
                'net_price'     => $p->net_price,
                'created_at'    => $p->created_at,
                'updated_at'    => $p->updated_at,
            ];
        })->filter()->values()->toArray();

        $this->insert('pharmacies', $rows);
    }

    // ------------------------------------------------------------------
    //  Out-of-stocks  (add clinic_id)
    // ------------------------------------------------------------------

    private function importOutOfStocks(): void
    {
        $this->info('→ Out-of-stocks...');

        $rows = $this->old('out_of_stocks')->get()->map(fn($o) => [
            'id'         => $o->id,
            'clinic_id'  => $this->clinicId,
            'phar_id'    => $o->phar_id,
            'total'      => $o->total ?? 0,
            'sale'       => $o->sale ?? 0,
            'created_at' => $o->created_at,
            'updated_at' => $o->updated_at,
        ])->toArray();

        $this->insert('out_of_stocks', $rows);
    }

    // ------------------------------------------------------------------
    //  Purchases  (no clinic_id column directly — linked via pharmacy)
    // ------------------------------------------------------------------

    private function importPurchases(): void
    {
        $this->info('→ Purchases...');

        $rows = $this->old('purchases')->get()->map(fn($p) => [
            'id'            => $p->id,
            'phar_id'       => $p->phar_id,
            'selling_price' => $p->selling_price,
            'net_price'     => $p->net_price,
            'qty'           => $p->qty,
            'created_time'  => $p->created_time ?? null,
            'created_at'    => $p->created_at,
            'updated_at'    => $p->updated_at,
        ])->toArray();

        $this->insert('purchases', $rows);
    }

    // ------------------------------------------------------------------
    //  Treatments  (add clinic_id, defaults for new columns)
    // ------------------------------------------------------------------

    private function importTreatments(): void
    {
        $this->info('→ Treatments...');

        $existingNames = DB::table('treatments')
            ->where('clinic_id', $this->clinicId)
            ->pluck('name')->flip()->toArray();

        $rows = $this->old('treatments')->get()->map(function ($t) use ($existingNames) {
            if (isset($existingNames[$t->name])) {
                return null;
            }
            return [
                'id'               => $t->id,
                'clinic_id'        => $this->clinicId,
                'name'             => $t->name,
                'description'      => null,
                'price'            => $t->price,
                'duration_minutes' => null,
                'is_active'        => true,
                'created_at'       => $t->created_at,
                'updated_at'       => $t->updated_at,
            ];
        })->filter()->values()->toArray();

        $this->insert('treatments', $rows);
    }

    // ------------------------------------------------------------------
    //  Photos  (add clinic_id; old photo field is a JSON string)
    // ------------------------------------------------------------------

    private function importPhotos(): void
    {
        $this->info('→ Photos...');

        $existingIds = DB::table('photos')->pluck('id')->flip()->toArray();

        $rows = $this->old('photos')->get()->map(function ($p) use ($existingIds) {
            if (isset($existingIds[$p->id])) {
                return null;
            }
            // Map old photo record into new table structure.
            // New table has filename/file_path etc. for new uploads;
            // legacy imports use the `photo` (JSON) and `created_time` columns
            // added by the 2026_04_01 migration.
            return [
                'id'            => $p->id,
                'clinic_id'     => $this->clinicId,
                'patient_id'    => $p->patient_id,
                'filename'      => null,        // not available in old data
                'original_name' => null,
                'file_path'     => null,
                'file_type'     => null,
                'file_size'     => null,
                'description'   => $p->description ?? null,
                'created_time'  => $p->created_time,
                'photo'         => $p->photo ?? '[]',
                'created_at'    => $p->created_at,
                'updated_at'    => $p->updated_at,
            ];
        })->filter()->values()->toArray();

        $this->insert('photos', $rows);
    }

    // ------------------------------------------------------------------
    //  Weights  (old: many individual measurement columns → new: weight + metadata JSON)
    // ------------------------------------------------------------------

    private function importWeights(): void
    {
        $this->info('→ Weights...');

        $existingIds = DB::table('weights')->pluck('id')->flip()->toArray();

        $rows = $this->old('weights')->get()->map(function ($w) use ($existingIds) {
            if (isset($existingIds[$w->id])) {
                return null;
            }

            // Pack all old body-measurement columns into metadata JSON
            $metadata = array_filter([
                'arm_contract'  => $w->arm_contract  ?? null,
                'arm_relax'     => $w->arm_relax     ?? null,
                'arm_middle'    => $w->arm_middle     ?? null,
                'arm_lower'     => $w->arm_lower      ?? null,
                'waist_upper'   => $w->waist_upper    ?? null,
                'waist_middle'  => $w->waist_middle   ?? null,
                'waist_lower'   => $w->waist_lower    ?? null,
                'thigh_upper'   => $w->thigh_upper    ?? null,
                'thigh_middle'  => $w->thigh_middle   ?? null,
                'thigh_lower'   => $w->thigh_lower    ?? null,
                'calf_upper'    => $w->calf_upper     ?? null,
                'calf_middle'   => $w->calf_middle    ?? null,
                'calf_lower'    => $w->calf_lower     ?? null,
            ], fn($v) => $v !== null && $v !== '');

            return [
                'id'          => $w->id,
                'clinic_id'   => $this->clinicId,
                'patient_id'  => $w->patient_id,
                'weight'      => is_numeric($w->weight) ? $w->weight : null,
                'weight_date' => $w->created_time,
                'notes'       => null,
                'metadata'    => !empty($metadata) ? json_encode($metadata) : null,
                'created_at'  => $w->created_at,
                'updated_at'  => $w->updated_at,
            ];
        })->filter()->values()->toArray();

        $this->insert('weights', $rows);
    }

    // ------------------------------------------------------------------
    //  Records  (add clinic_id, map created_time → record_date)
    // ------------------------------------------------------------------

    private function importRecords(): void
    {
        $this->info('→ Records...');

        $rows = $this->old('records')->get()->map(fn($r) => [
            'id'          => $r->id,
            'clinic_id'   => $this->clinicId,
            'patient_id'  => $r->patient_id,
            'title'       => 'Imported Record',
            'description' => $r->description ?? null,
            'record_date' => $r->created_time,
            'record_type' => 'general',
            'metadata'    => null,
            'created_at'  => $r->created_at,
            'updated_at'  => $r->updated_at,
        ])->toArray();

        $this->insert('records', $rows);
    }

    // ------------------------------------------------------------------
    //  Invoices  (completely restructured)
    //
    //  OLD invoice row has ONE line item embedded directly:
    //    invoice_no, patient_id, treatment_id, phar_id, price, discount,
    //    qty, sub_total, type (Treatment|Sale), discount_type, created_time
    //
    //  NEW structure: invoices (header) + invoice_items (line items)
    //    Multiple old rows with same invoice_no → 1 header + N items
    // ------------------------------------------------------------------

    private function importInvoices(): void
    {
        $this->info('→ Invoices → invoices + invoice_items...');

        $oldInvoices = $this->old('invoices')->get();
        if ($oldInvoices->isEmpty()) {
            $this->line('  No old invoice data found — skipping.');
            return;
        }

        // Group all old rows by invoice_no
        $grouped = $oldInvoices->groupBy('invoice_no');

        $invoiceRows     = [];
        $invoiceItemRows = [];
        $invoiceIdMap    = []; // old invoice_no → new invoice id

        $newInvoiceId = (DB::table('invoices')->max('id') ?? 0) + 1;
        $newItemId    = (DB::table('invoice_items')->max('id') ?? 0) + 1;

        foreach ($grouped as $invoiceNo => $lines) {
            $firstLine   = $lines->first();
            $totalAmount = $lines->sum('sub_total');
            $invoiceDate = $firstLine->created_time;

            $invoiceRows[] = [
                'id'              => $newInvoiceId,
                'clinic_id'       => $this->clinicId,
                'patient_id'      => $firstLine->patient_id,
                'invoice_number'  => $invoiceNo,
                'invoice_date'    => $invoiceDate,
                'due_date'        => null,
                'subtotal'        => $totalAmount,
                'tax_amount'      => 0,
                'discount_amount' => $lines->sum('discount'),
                'total_amount'    => $totalAmount,
                'status'          => 'paid',
                'notes'           => null,
                'created_at'      => $firstLine->created_at,
                'updated_at'      => $firstLine->updated_at,
            ];

            $invoiceIdMap[$invoiceNo] = $newInvoiceId;

            foreach ($lines as $line) {
                // Determine item type
                $itemType = strtolower($line->type ?? '') === 'treatment' ? 'treatment' : 'sale';

                $discType = in_array(strtolower($line->discount_type ?? ''), ['percentage', 'percent'])
                    ? 'percentage'
                    : 'fixed';

                $invoiceItemRows[] = [
                    'id'              => $newItemId++,
                    'invoice_id'      => $newInvoiceId,
                    'clinic_id'       => $this->clinicId,
                    'item_type'       => $itemType,
                    'treatment_id'    => $itemType === 'treatment' ? ($line->treatment_id ?? null) : null,
                    'pharmacy_id'     => $itemType === 'sale'      ? ($line->phar_id ?? null)      : null,
                    'qty'             => $line->qty ?? 1,
                    'unit_price'      => $line->price ?? 0,
                    'discount_type'   => $discType,
                    'discount_amount' => $line->discount ?? 0,
                    'subtotal'        => $line->sub_total ?? 0,
                    'created_at'      => $line->created_at,
                    'updated_at'      => $line->updated_at,
                ];
            }

            $newInvoiceId++;
        }

        $this->insert('invoices', $invoiceRows);
        $this->insert('invoice_items', $invoiceItemRows);
    }

    // ------------------------------------------------------------------
    //  Sales  (completely restructured)
    //
    //  OLD sales: invoice_no, phar_id, qty, price, purchase_price, created_time
    //  NEW sales: clinic_id, patient_id, sale_number, sale_date,
    //             price, qty, subtotal, tax_amount, discount_amount,
    //             total_amount, status, notes
    // ------------------------------------------------------------------

    private function importSales(): void
    {
        $this->info('→ Sales...');

        $existingSaleNumbers = DB::table('sales')->pluck('sale_number')->flip()->toArray();

        $rows = $this->old('sales')->get()->map(function ($s) use ($existingSaleNumbers) {
            if (isset($existingSaleNumbers[$s->invoice_no])) {
                return null;
            }
            $subtotal = ($s->price ?? 0) * ($s->qty ?? 1);
            return [
                'clinic_id'       => $this->clinicId,
                'patient_id'      => null,           // old sales had no patient_id
                'sale_number'     => $s->invoice_no,
                'sale_date'       => $s->created_time,
                'price'           => $s->price ?? 0,
                'qty'             => $s->qty ?? 1,
                'subtotal'        => $subtotal,
                'tax_amount'      => 0,
                'discount_amount' => 0,
                'total_amount'    => $subtotal,
                'status'          => 'completed',
                'notes'           => "phar_id:{$s->phar_id} purchase_price:{$s->purchase_price}",
                'created_at'      => $s->created_at,
                'updated_at'      => $s->updated_at,
            ];
        })->filter()->values()->toArray();

        $this->insert('sales', $rows);
    }
}
