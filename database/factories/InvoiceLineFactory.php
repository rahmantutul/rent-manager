<?php

namespace Database\Factories;

use App\Models\InvoiceLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceLineFactory extends Factory
{
    protected $model = InvoiceLine::class;

    public function definition(): array
    {
        return ['owner_id' => null, 'invoice_id' => null, 'description' => 'Rent', 'quantity' => 1, 'unit_price' => 10000, 'line_subtotal' => 10000, 'tax_rate_value' => 0, 'tax_amount' => 0, 'line_total' => 10000];
    }
}
