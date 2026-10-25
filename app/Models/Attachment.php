<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use BelongsToOwner;

    public $timestamps = false;

    protected $fillable = ['attachable_type', 'attachable_id', 'disk', 'path', 'original_name', 'mime', 'size', 'uploaded_by', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'size' => 'integer'];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
