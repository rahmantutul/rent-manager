<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToOwner;

    public $timestamps = false;

    protected $fillable = ['key', 'value'];
}
