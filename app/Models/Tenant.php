<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use BelongsToOwner,HasFactory,SoftDeletes;

    public $timestamps = false;

    protected $fillable = ['type', 'name', 'company_name', 'email', 'phone', 'alt_phone', 'id_document_type', 'id_document_number', 'date_of_birth', 'emergency_contact_name', 'emergency_contact_phone', 'previous_address', 'status', 'portal_enabled', 'notes'];

    protected function casts(): array
    {
        return ['id_document_number' => 'encrypted', 'date_of_birth' => 'date', 'portal_enabled' => 'boolean'];
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
