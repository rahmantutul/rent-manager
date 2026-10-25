<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'email', 'phone', 'currency_code', 'minor_units', 'timezone', 'locale', 'date_format', 'logo_path'];

    protected function casts(): array
    {
        return ['minor_units' => 'integer'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }
}
