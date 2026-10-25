<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'email', 'password', 'locale'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'role' => Role::class, 'permissions' => 'array', 'is_active' => 'boolean', 'last_login_at' => 'datetime'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function hasRole(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function canSignIn(): bool
    {
        return $this->is_active && ($this->role === Role::SuperAdmin ? $this->owner_id === null : $this->owner?->status === 'active');
    }
}
