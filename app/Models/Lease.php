<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lease extends Model
{
    use BelongsToOwner,HasFactory;

    public $timestamps = false;

    protected $fillable = ['lease_number', 'unit_id', 'tenant_id', 'start_date', 'end_date', 'move_out_date', 'status', 'active_unit_id', 'billing_cycle', 'billing_day', 'due_after_days', 'grace_days', 'next_invoice_date', 'auto_invoice', 'prorate_first_period', 'security_deposit', 'late_fee_rule_id', 'notice_period_days', 'termination_reason', 'terms'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'move_out_date' => 'date', 'next_invoice_date' => 'date', 'auto_invoice' => 'boolean', 'prorate_first_period' => 'boolean', 'security_deposit' => 'integer'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(LeaseCharge::class);
    }

    public function coTenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'lease_tenants')->withPivot('share_percent');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
