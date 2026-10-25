<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaseCharge extends Model
{
    use BelongsToOwner,HasFactory;

    public $timestamps = false;

    protected $fillable = ['lease_id', 'charge_type_id', 'description', 'amount', 'frequency', 'starts_on', 'ends_on', 'tax_rate_id', 'is_active'];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'starts_on' => 'date', 'ends_on' => 'date', 'is_active' => 'boolean'];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function chargeType(): BelongsTo
    {
        return $this->belongsTo(ChargeType::class);
    }
}
