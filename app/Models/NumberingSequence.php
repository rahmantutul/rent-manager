<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberingSequence extends Model
{
    use BelongsToOwner,HasFactory;

    public $timestamps = false;

    protected $fillable = ['doc_type', 'prefix', 'format', 'next_number', 'reset_policy', 'last_reset_on'];

    protected function casts(): array
    {
        return ['next_number' => 'integer', 'last_reset_on' => 'date'];
    }
}
