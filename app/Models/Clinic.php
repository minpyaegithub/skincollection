<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'prefix',
        'address',
        'phone',
        'email',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
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

    public function counters()
    {
        return $this->hasMany(ClinicCounter::class);
    }

    /**
     * Get the next counter number for a specific type
     */
    public function getNextCounterNumber($type)
    {
        $counter = $this->counters()->where('counter_type', $type)->first();
        
        if (!$counter) {
            $counter = $this->counters()->create([
                'counter_type' => $type,
                'current_number' => 0
            ]);
        }
        
        $counter->increment('current_number');
        return $counter->current_number;
    }
}