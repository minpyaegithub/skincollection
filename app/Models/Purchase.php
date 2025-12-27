<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $dates = [
        'created_time'
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class, 'phar_id');
    }

    public function scopeForClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }
}
