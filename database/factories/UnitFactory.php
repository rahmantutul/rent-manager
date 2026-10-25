<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return ['building_id' => Building::factory(), 'unit_number' => fake()->unique()->numerify('###'), 'floor_level' => 1, 'unit_type' => 'apartment', 'default_rent' => 120000, 'status' => 'vacant'];
    }
}
