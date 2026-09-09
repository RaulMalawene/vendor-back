<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'contact_name' => fake()->name(),
            'phone' => fake()->numerify('8#######'),
            'email' => fake()->companyEmail(),
            'bank_name' => fake()->randomElement(['BCI', 'BIM Millennium', 'Standard Bank', 'Absa']),
            'bank_account_holder' => fake()->name(),
            'bank_account_number' => fake()->numerify('################'),
        ];
    }
}