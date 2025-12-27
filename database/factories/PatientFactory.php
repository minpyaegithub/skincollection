<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'age' => $this->faker->numberBetween(1, 90),
            'address' => $this->faker->address,
            'weight' => $this->faker->randomFloat(2, 40, 120),
            'feet' => $this->faker->numberBetween(4, 6),
            'inches' => $this->faker->numberBetween(0, 11),
            'BMI' => $this->faker->randomFloat(2, 15, 40),
            'disease' => $this->faker->sentence,
            'photo' => json_encode([]),
            'clinic_id' => Clinic::factory(),
            // Keep unique-ish token for factory usage (controller handles real sequencing)
            'token' => $this->faker->unique()->bothify('FAKE####'),
        ];
    }
}
