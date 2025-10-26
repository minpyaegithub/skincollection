<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'counter_type',
        'current_number',
        'prefix',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
