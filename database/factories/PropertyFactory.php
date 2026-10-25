<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return ['name' => fake()->company(), 'code' => strtoupper(fake()->unique()->lexify('PROP-???')), 'type' => 'residential', 'address_line1' => fake()->streetAddress(), 'city' => fake()->city(), 'state' => fake()->state(), 'postal_code' => fake()->postcode(), 'country_code' => 'BD'];
    }
}
