<?php

namespace App\Exports;

use App\Models\Treatment;
use Maatwebsite\Excel\Concerns\FromCollection;

class TreatmentExport implements FromCollection
{
    public function __construct(private readonly ?int $clinicId = null)
    {
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Treatment::query();

        if ($this->clinicId) {
            $query->forClinic($this->clinicId);
        }

        return $query->orderBy('name')->get();
    }
}