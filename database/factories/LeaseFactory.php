<?php

namespace Database\Factories;

use App\Models\Lease;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaseFactory extends Factory
{
    protected $model = Lease::class;

    public function definition(): array
    {
        return ['owner_id' => null, 'lease_number' => 'LSE-'.$this->faker->unique()->numerify('#####'), 'unit_id' => null, 'tenant_id' => null, 'start_date' => now()->toDateString(), 'status' => 'draft', 'billing_cycle' => 'monthly', 'billing_day' => 1, 'due_after_days' => 0, 'grace_days' => 0, 'auto_invoice' => true, 'prorate_first_period' => false, 'security_deposit' => 0];
    }
}
