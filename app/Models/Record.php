<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'title',
        'description',
        'record_date',
        'record_type',
        'metadata',
    ];

    protected $casts = [
        'record_date' => 'date',
        'metadata' => 'array',
    ];
}
