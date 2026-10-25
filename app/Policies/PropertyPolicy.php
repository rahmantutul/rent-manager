<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Owner, Role::Manager, Role::Accountant);
    }

    public function view(User $user, Property $property): bool
    {
        return $this->viewAny($user) && $property->owner_id === $user->owner_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Owner, Role::Manager);
    }

    public function update(User $user, Property $property): bool
    {
        return $this->create($user) && $this->view($user, $property);
    }

    public function delete(User $user, Property $property): bool
    {
        return $this->update($user, $property);
    }
}
