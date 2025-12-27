<?php

namespace Database\Factories;

use App\Models\TreatmentPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class TreatmentPackageFactory extends Factory
{
    protected $model = TreatmentPackage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->optional()->sentence(10),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'sessions' => $this->faker->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
