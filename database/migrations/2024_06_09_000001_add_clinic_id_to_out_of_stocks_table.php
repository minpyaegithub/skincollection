<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('out_of_stocks') || Schema::hasColumn('out_of_stocks', 'clinic_id')) {
            return;
        }

        Schema::table('out_of_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('clinic_id')->nullable()->after('id');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('out_of_stocks') || !Schema::hasColumn('out_of_stocks', 'clinic_id')) {
            return;
        }

        Schema::table('out_of_stocks', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn('clinic_id');
        });
    }
};