<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeType extends Model
{
    use BelongsToOwner,HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'kind', 'default_tax_rate_id', 'is_system', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean', 'is_active' => 'boolean'];
    }
}
