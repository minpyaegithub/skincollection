<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Treatment;
use Illuminate\Database\Eloquent\Factories\Factory;

class TreatmentFactory extends Factory
{
    protected $model = Treatment::class;

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->optional()->sentence(12),
            'price' => $this->faker->randomFloat(2, 5, 500),
            'duration_minutes' => $this->faker->numberBetween(10, 180),
            'is_active' => true,
        ];
    }
}
