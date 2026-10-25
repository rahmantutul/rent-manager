<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return ['owner_id' => null, 'invoice_number' => 'INV-'.$this->faker->unique()->numerify('#####'), 'invoice_type' => 'invoice', 'source' => 'recurring', 'status' => 'issued', 'payment_status' => 'unpaid', 'subtotal' => 0, 'tax_total' => 0, 'total' => 0, 'amount_paid' => 0, 'balance_due' => 0, 'currency_code' => 'USD', 'issue_date' => now()->toDateString(), 'due_date' => now()->toDateString()];
    }
}
