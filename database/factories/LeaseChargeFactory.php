<?php

namespace Database\Factories;

use App\Models\LeaseCharge;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaseChargeFactory extends Factory
{
    protected $model = LeaseCharge::class;

    public function definition(): array
    {
        return ['owner_id' => null, 'lease_id' => null, 'charge_type_id' => null, 'amount' => 10000, 'frequency' => 'every_cycle', 'starts_on' => now()->toDateString(), 'is_active' => true];
    }
}
