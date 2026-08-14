# Old Database Import Guide

This guide explains how to migrate data from the **old single-clinic database** into the **new multi-clinic structure**.

---

## What Gets Imported

| Old Table | → New Table(s) | Notes |
|---|---|---|
| `patients` | `patients` | Adds `clinic_id` |
| `appointments` | `appointments` | Adds `clinic_id` |
| `appointment_times` | `appointment_times` | Direct copy (if empty) |
| `pharmacies` | `pharmacies` | Adds `clinic_id` |
| `out_of_stocks` | `out_of_stocks` | Adds `clinic_id` |
| `purchases` | `purchases` | Direct copy |
| `treatments` | `treatments` | Adds `clinic_id`, `is_active`, `duration_minutes` |
| `photos` | `photos` | Adds `clinic_id`, legacy `photo` JSON column |
| `weights` | `weights` | Reshapes body-measurement columns → `metadata` JSON |
| `records` | `records` | Adds `clinic_id`, maps `created_time` → `record_date` |
| `invoices` (old) | `invoices` + `invoice_items` | Splits flat row into header + line item |
| `sales` (old) | `sales` | Restructures columns |
| `users` | `users` | Adds `clinic_id` |
| `roles` / `permissions` | same | Direct copy if tables empty |

---

## Step-by-Step on the Server

### Step 1 — Pull the latest code

```bash
cd /www/wwwroot/default/skincollection
git pull origin master
```

### Step 2 — Run migrations (adds legacy columns to photos & weights)

```bash
php artisan migrate --force
```

You should see:
```
2026_04_01_000000_add_import_support_columns ........ DONE
```

### Step 3 — Load the old SQL dump into a temporary database

```bash
# Create the old database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS skincollection_old CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import the dump (copy the .sql file to the server first, or use scp)
mysql -u root -p skincollection_old < /path/to/skincollection-structure-data.sql
```

> **Tip:** You can SCP the file from your Mac:
> ```bash
> scp skincollection-structure-data.sql user@yourserver:/tmp/
> ```
> Then on the server:
> ```bash
> mysql -u root -p skincollection_old < /tmp/skincollection-structure-data.sql
> ```

### Step 4 — Confirm the target clinic exists

The import assigns all records to a specific clinic ID. Make sure it exists:

```bash
php artisan tinker --execute="App\Models\Clinic::all(['id','name'])->each(fn(\$c) => print(\$c->id.' → '.\$c->name.\"\n\"));"
```

If no clinics exist, create one first:
```bash
php artisan db:seed --class=ClinicSeeder
```

### Step 5 — Dry run first (no writes)

Always run a dry run to check counts before actually importing:

```bash
php artisan import:old-db \
  --old-db=skincollection_old \
  --clinic-id=1 \
  --dry-run
```

Example output:
```
→ Roles & permissions...
→ Users...
→ Patients...
→ Appointment times...
→ Appointments...
...
==============================================
  Import Summary
==============================================
  patients                  3393 rows
  photos                    12988 rows
  weights                   86 rows
  ...
DRY RUN — no data was written.
```

### Step 6 — Run the real import

```bash
php artisan import:old-db \
  --old-db=skincollection_old \
  --clinic-id=1 \
  --force
```

### Step 7 — Clear caches

```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

### Step 8 — Drop the temporary old database (optional)

```bash
mysql -u root -p -e "DROP DATABASE skincollection_old;"
```

---

## Re-running the Import

The import is **safe to re-run** — it skips rows that already exist:
- `patients` — skips by `token` (unique)
- `pharmacies` — skips by `name` + `clinic_id`
- `treatments` — skips by `name` + `clinic_id`
- `users` — skips by `email`
- `photos`, `weights` — skips by `id`
- `appointment_times` — skips if table is not empty
- `roles`/`permissions` — skips if tables are not empty

---

## Troubleshooting

### "Old database not found"
Make sure you imported the SQL dump:
```bash
mysql -u root -p skincollection_old < skincollection-structure-data.sql
```

### "Clinic ID X does not exist"
Run seeders first or check existing clinic IDs:
```bash
php artisan db:seed --class=ClinicSeeder
```

### Foreign key constraint errors
The command wraps everything in a transaction. If a foreign key fails, the whole import rolls back. Common causes:
- A `patient_id` in `photos` that doesn't exist in `patients` — this means the old DB had orphaned records
- Fix: Import patients first and check for missing IDs

### "Column not found" (e.g., `created_time` on photos)
You haven't run the new migration yet:
```bash
php artisan migrate --force
```

### MySQL permissions for cross-database query
The DB user in `.env` must have access to **both** databases:
```sql
GRANT ALL PRIVILEGES ON skincollection_old.* TO 'your_db_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## Checking the Import Results

```bash
# Count rows per table
php artisan tinker --execute="
collect(['patients','photos','weights','appointments','pharmacies','purchases','treatments','records','invoices','invoice_items','sales'])
->each(fn(\$t) => print(\$t.': '.DB::table(\$t)->count().\"\n\"));
"
```
