<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Users belong to a clinic
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained('clinics')->onDelete('set null');
        });

        // Data tables are scoped to a clinic
        $tables = ['patients', 'appointments', 'invoices', 'purchases', 'pharmacies', 'expenses', 'photos', 'records', 'sales', 'treatments', 'weights'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'clinic_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('clinic_id')->nullable()->after('id')->constrained('clinics')->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn('clinic_id');
        });

        // Drop columns from other tables if needed for rollback
    }
};