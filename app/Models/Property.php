<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use BelongsToOwner, HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = ['name', 'code', 'type', 'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country_code', 'manager_user_id', 'notes'];

    protected static function booted(): void
    {
        static::created(function (Property $property): void {
            $property->buildings()->create(['name' => $property->name]);
        });
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }
}
