<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return ['owner_id' => null, 'type' => 'individual', 'name' => $this->faker->name(), 'email' => $this->faker->safeEmail(), 'phone' => $this->faker->phoneNumber(), 'status' => 'prospect', 'portal_enabled' => false];
    }
}
