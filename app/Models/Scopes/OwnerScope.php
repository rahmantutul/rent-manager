<?php

namespace App\Models\Scopes;

use App\Support\CurrentOwner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class OwnerScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $ownerId = app(CurrentOwner::class)->id();
        if ($ownerId === null) {
            $builder->whereRaw('1 = 0');
        } else {
            $builder->where($model->qualifyColumn('owner_id'), $ownerId);
        }
    }
}
