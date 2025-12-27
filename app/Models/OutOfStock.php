<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutOfStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'phar_id',
        'total',
        'sale',
        'clinic_id',
    ];

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class, 'phar_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function scopeForClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }
}
