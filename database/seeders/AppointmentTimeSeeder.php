<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppointmentTime;

class AppointmentTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $times = [
            ['time' => '09:00', 'custom_time' => '09:00 AM'],
            ['time' => '09:30', 'custom_time' => '09:30 AM'],
            ['time' => '10:00', 'custom_time' => '10:00 AM'],
            ['time' => '10:30', 'custom_time' => '10:30 AM'],
            ['time' => '11:00', 'custom_time' => '11:00 AM'],
            ['time' => '11:30', 'custom_time' => '11:30 AM'],
            ['time' => '12:00', 'custom_time' => '12:00 PM'],
            ['time' => '12:30', 'custom_time' => '12:30 PM'],
            ['time' => '13:00', 'custom_time' => '01:00 PM'],
            ['time' => '13:30', 'custom_time' => '01:30 PM'],
            ['time' => '14:00', 'custom_time' => '02:00 PM'],
            ['time' => '14:30', 'custom_time' => '02:30 PM'],
            ['time' => '15:00', 'custom_time' => '03:00 PM'],
            ['time' => '15:30', 'custom_time' => '03:30 PM'],
            ['time' => '16:00', 'custom_time' => '04:00 PM'],
            ['time' => '16:30', 'custom_time' => '04:30 PM'],
            ['time' => '17:00', 'custom_time' => '05:00 PM'],
            ['time' => '17:30', 'custom_time' => '05:30 PM'],
        ];

        foreach ($times as $time) {
            AppointmentTime::create($time);
        }
    }
}
