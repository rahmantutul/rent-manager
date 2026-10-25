<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use BelongsToOwner,HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'rate', 'is_inclusive', 'is_active'];

    protected function casts(): array
    {
        return ['rate' => 'decimal:3', 'is_inclusive' => 'boolean', 'is_active' => 'boolean'];
    }
}
