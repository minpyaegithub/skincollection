<?php

namespace App\Exports;

use App\Models\Pharmacy;
use Maatwebsite\Excel\Concerns\FromCollection;

class PharmacyExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Pharmacy::all();
    }
}