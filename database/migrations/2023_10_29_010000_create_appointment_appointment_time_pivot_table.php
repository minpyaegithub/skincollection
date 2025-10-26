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
        Schema::create('appointment_appointment_time', function (Blueprint $table) {
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_time_id')->constrained()->onDelete('cascade');
            $table->primary(['appointment_id', 'appointment_time_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointment_appointment_time');
    }
};