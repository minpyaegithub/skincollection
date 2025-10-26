<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getPatientFullName()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function weights()
    {
        return $this->hasMany(Weight::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function records()
    {
        return $this->hasMany(Record::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
