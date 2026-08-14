<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add metadata JSON column to weights for storing old body measurements
        if (!Schema::hasColumn('weights', 'metadata')) {
            Schema::table('weights', function (Blueprint $table) {
                $table->json('metadata')->nullable()->after('notes');
            });
        }

        // The old photos table stored rows differently:
        //   - `created_time` date column
        //   - `photo` JSON array of filenames
        // The new photos table uses individual file rows.
        // We add legacy columns to support the imported data without breaking new uploads.
        if (!Schema::hasColumn('photos', 'created_time')) {
            Schema::table('photos', function (Blueprint $table) {
                $table->date('created_time')->nullable()->after('description');
                $table->text('photo')->nullable()->after('created_time')
                    ->comment('Legacy: JSON array of filenames from old DB');
            });
        }

        // Make new photos columns nullable using raw ALTER TABLE (avoids doctrine/dbal requirement)
        DB::statement('ALTER TABLE photos
            MODIFY COLUMN filename varchar(255) NULL,
            MODIFY COLUMN original_name varchar(255) NULL,
            MODIFY COLUMN file_path varchar(255) NULL,
            MODIFY COLUMN file_type varchar(255) NULL,
            MODIFY COLUMN file_size int(11) NULL
        ');
    }

    public function down(): void
    {
        if (Schema::hasColumn('weights', 'metadata')) {
            Schema::table('weights', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });
        }
        if (Schema::hasColumn('photos', 'created_time')) {
            Schema::table('photos', function (Blueprint $table) {
                $table->dropColumn(['created_time', 'photo']);
            });
        }
    }
};
