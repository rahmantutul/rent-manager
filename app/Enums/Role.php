<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case Owner = 'owner';
    case Manager = 'manager';
    case Accountant = 'accountant';
    case Tenant = 'tenant';

    public function landingRoute(): string
    {
        return match ($this) {
            self::SuperAdmin => 'super-admin.dashboard',
            self::Tenant => 'portal.dashboard',
            default => 'admin.dashboard',
        };
    }
}
