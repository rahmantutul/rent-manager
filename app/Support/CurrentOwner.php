<?php

namespace App\Support;

use App\Models\Owner;
use Illuminate\Support\Facades\Auth;

final class CurrentOwner
{
    public function id(): ?int
    {
        return Auth::user()?->owner_id;
    }

    public function get(): ?Owner
    {
        return Auth::user()?->owner;
    }
}
