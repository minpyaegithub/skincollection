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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender');
            $table->integer('age')->nullable();
            $table->text('address')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->integer('feet')->nullable();
            $table->integer('inches')->nullable();
            $table->decimal('BMI', 5, 2)->nullable();
            $table->text('disease')->nullable();
            $table->json('photo')->nullable(); // Stores photo filenames as JSON
            $table->string('token')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patients');
    }
};

