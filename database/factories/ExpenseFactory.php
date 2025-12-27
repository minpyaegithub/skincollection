<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'category' => $this->faker->randomElement(['Utilities', 'Supplies', 'Maintenance', 'Salaries']),
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->randomFloat(2, 10, 5000),
            'expense_date' => $this->faker->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
