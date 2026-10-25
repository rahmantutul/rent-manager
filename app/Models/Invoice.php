<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToOwner,HasFactory;

    public $timestamps = false;

    protected $fillable = ['owner_id', 'invoice_number', 'invoice_type', 'source', 'recurring_key', 'lease_id', 'tenant_id', 'unit_id', 'credit_for_invoice_id', 'period_start', 'period_end', 'issue_date', 'due_date', 'status', 'payment_status', 'subtotal', 'tax_total', 'total', 'amount_paid', 'balance_due', 'currency_code', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['period_start' => 'date', 'period_end' => 'date', 'issue_date' => 'date', 'due_date' => 'date', 'subtotal' => 'integer', 'tax_total' => 'integer', 'total' => 'integer', 'amount_paid' => 'integer', 'balance_due' => 'integer'];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
