<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OwnerFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->company(), 'email' => fake()->safeEmail(), 'phone' => '', 'currency_code' => 'USD', 'minor_units' => 2, 'timezone' => 'UTC', 'locale' => 'en', 'date_format' => 'Y-m-d', 'status' => 'active'];
    }
}
