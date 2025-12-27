<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    public const TYPE_TREATMENT = 'treatment';
    public const TYPE_SALE = 'sale';

    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'qty' => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }

    public function treatmentPackage()
    {
        return $this->belongsTo(TreatmentPackage::class);
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function scopeForClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }
}
