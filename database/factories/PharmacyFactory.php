<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Factories\Factory;

class PharmacyFactory extends Factory
{
    protected $model = Pharmacy::class;

    public function definition(): array
    {
        $netPrice = $this->faker->randomFloat(2, 1, 200);

        return [
            'clinic_id' => Clinic::factory(),
            'name' => $this->faker->unique()->lexify('Medication ???'),
            'net_price' => $netPrice,
            'selling_price' => $netPrice + $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
