<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a default clinic
        Clinic::create([
            'name' => 'Main Clinic',
            'prefix' => 'MAIN',
            // Add other details like address, phone etc. if you have them in your migration
        ]);
    }
}