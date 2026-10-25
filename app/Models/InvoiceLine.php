<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    use BelongsToOwner,HasFactory;

    public $timestamps = false;

    protected $fillable = ['owner_id', 'invoice_id', 'charge_type_id', 'lease_charge_id', 'description', 'quantity', 'unit_price', 'line_subtotal', 'tax_rate_id', 'tax_rate_value', 'tax_amount', 'line_total', 'sort_order'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:2', 'unit_price' => 'integer', 'line_subtotal' => 'integer', 'tax_rate_value' => 'decimal:3', 'tax_amount' => 'integer', 'line_total' => 'integer'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
