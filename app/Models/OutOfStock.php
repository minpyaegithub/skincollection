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
    ];

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class, 'phar_id');
    }
}
