<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('treatment_package_id')
                ->nullable()
                ->after('treatment_id')
                ->constrained('treatment_packages')
                ->nullOnDelete();

            $table->index(['treatment_package_id']);
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex(['treatment_package_id']);
            $table->dropConstrainedForeignId('treatment_package_id');
        });
    }
};
