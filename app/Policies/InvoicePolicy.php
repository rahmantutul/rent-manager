<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $u): bool
    {
        return $u->hasRole(Role::Owner, Role::Manager, Role::Accountant);
    }

    public function view(User $u, Invoice $m): bool
    {
        return $this->viewAny($u) && $u->owner_id === $m->owner_id;
    }
}
