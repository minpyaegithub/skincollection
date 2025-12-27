<?php

namespace App\Exports;

use App\Models\Purchase;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class PurchaseExport implements FromCollection
{
    protected ?int $clinicId;

    public function __construct(?int $clinicId = null)
    {
        $this->clinicId = $clinicId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(): Collection
    {
        $query = Purchase::with(['clinic', 'pharmacy'])->orderByDesc('created_time');

        if ($this->clinicId) {
            $query->forClinic($this->clinicId);
        }

        return $query->get()->map(function (Purchase $purchase) {
            return [
                'Clinic' => optional($purchase->clinic)->name,
                'Pharmacy' => optional($purchase->pharmacy)->name,
                'Quantity' => $purchase->qty,
                'Selling Price' => $purchase->selling_price,
                'Purchase Price' => $purchase->net_price,
                'Purchase Date' => optional($purchase->created_time)->format('Y-m-d'),
                'Recorded At' => optional($purchase->created_at)->format('Y-m-d H:i'),
            ];
        });
    }
}