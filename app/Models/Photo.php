<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'metadata'     => 'array',
        'photo'        => 'array',   // legacy: JSON array of filenames from old DB
        'created_time' => 'date',
    ];
}
