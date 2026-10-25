<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return ['owner_id' => Owner::factory(), 'name' => fake()->name(), 'email' => fake()->unique()->safeEmail(), 'password' => 'password', 'role' => Role::Owner, 'is_active' => true];
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => ['owner_id' => null, 'role' => Role::SuperAdmin]);
    }
}
