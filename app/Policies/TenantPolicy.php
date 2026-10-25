<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $u): bool
    {
        return $u->hasRole(Role::Owner, Role::Manager, Role::Accountant);
    }

    public function view(User $u, Tenant $m): bool
    {
        return $this->viewAny($u) && $u->owner_id === $m->owner_id;
    }

    public function create(User $u): bool
    {
        return $u->hasRole(Role::Owner, Role::Manager);
    }

    public function update(User $u, Tenant $m): bool
    {
        return $this->create($u) && $this->view($u, $m);
    }

    public function delete(User $u, Tenant $m): bool
    {
        return $this->update($u, $m);
    }
}
