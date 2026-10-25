<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use BelongsToOwner, HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = ['building_id', 'unit_number', 'floor_level', 'unit_type', 'bedrooms', 'bathrooms', 'area', 'area_unit', 'default_rent', 'status', 'features', 'notes'];

    protected $casts = ['floor_level' => 'integer', 'default_rent' => 'integer', 'bedrooms' => 'integer', 'bathrooms' => 'integer', 'area' => 'decimal:2', 'features' => 'array'];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function getFloorLabelAttribute(): string
    {
        return floor_label($this->floor_level);
    }
}
