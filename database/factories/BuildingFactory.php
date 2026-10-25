<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingFactory extends Factory
{
    protected $model = Building::class;

    public function definition(): array
    {
        return ['property_id' => Property::factory(), 'name' => fake()->streetName()];
    }
}
