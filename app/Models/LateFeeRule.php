<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateFeeRule extends Model
{
    use BelongsToOwner,HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'type', 'flat_amount', 'percent', 'repeat_every', 'max_amount'];

    protected function casts(): array
    {
        return ['percent' => 'decimal:3'];
    }
}
