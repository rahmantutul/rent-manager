<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function view(User $user, Unit $unit): bool
    {
        return $user->hasRole(Role::Owner, Role::Manager, Role::Accountant) && $unit->owner_id === $user->owner_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Owner, Role::Manager);
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->create($user) && $this->view($user, $unit);
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $this->update($user, $unit);
    }
}
