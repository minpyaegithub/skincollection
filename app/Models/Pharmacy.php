<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $dates = [
        'expire_date'
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function scopeForClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function stockSummary()
    {
        return $this->hasOne(OutOfStock::class, 'phar_id');
    }
}
