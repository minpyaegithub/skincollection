<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('treatment_packages');
    }

    public function down(): void
    {
        // Legacy table intentionally left dropped. Recreate only if historical data needs restoration.
    }
};
