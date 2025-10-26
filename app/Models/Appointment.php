<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $dates = [
        'date'
    ];

    public function timeSlots()
    {
        return $this->belongsToMany(AppointmentTime::class, 'appointment_appointment_time');
    }
}
