<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Building;
use App\Models\User;

class BuildingPolicy
{
    public function view(User $user, Building $building): bool
    {
        return $user->hasRole(Role::Owner, Role::Manager, Role::Accountant) && $building->owner_id === $user->owner_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Owner, Role::Manager);
    }

    public function update(User $user, Building $building): bool
    {
        return $this->create($user) && $this->view($user, $building);
    }

    public function delete(User $user, Building $building): bool
    {
        return $this->update($user, $building);
    }
}
