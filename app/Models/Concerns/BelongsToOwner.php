<?php

namespace App\Models\Concerns;

use App\Models\Owner;
use App\Models\Scopes\OwnerScope;
use App\Support\CurrentOwner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

trait BelongsToOwner
{
    public static function bootBelongsToOwner(): void
    {
        static::addGlobalScope(new OwnerScope);
        static::creating(function ($model) {
            $ownerId = app(CurrentOwner::class)->id();
            if ($ownerId === null || ($model->owner_id !== null && (int) $model->owner_id !== $ownerId)) {
                throw new LogicException('An explicit, matching owner context is required.');
            }
            $model->owner_id = $ownerId;
        });
        static::updating(function ($model) {
            if ($model->isDirty('owner_id') || (int) $model->owner_id !== app(CurrentOwner::class)->id()) {
                throw new LogicException('Owner reassignment is forbidden.');
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }
}
