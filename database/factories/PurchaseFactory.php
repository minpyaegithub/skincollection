<?php

namespace Database\Factories;

use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Pharmacy;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        $netPrice = $this->faker->randomFloat(2, 1, 200);

        return [
            'clinic_id' => null,
            'phar_id' => null,
            'selling_price' => $netPrice + $this->faker->randomFloat(2, 0, 50),
            'net_price' => $netPrice,
            'qty' => $this->faker->numberBetween(1, 50),
            'created_time' => Carbon::now()->subDays($this->faker->numberBetween(0, 30)),
        ];
    }

    public function forPharmacy(Pharmacy $pharmacy)
    {
        return $this->state(function () use ($pharmacy) {
            return [
                'phar_id' => $pharmacy->id,
                'clinic_id' => $pharmacy->clinic_id,
            ];
        });
    }
}
