<?php

namespace App\Support\Traits;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRolesAndPermissions
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    /**
     * Check if user has specific role by slug.
     */
    public function hasRole(string|array $roles): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        $roleSlugs = is_array($roles) ? $roles : func_get_args();

        return $this->roles->pluck('slug')->intersect($roleSlugs)->isNotEmpty();
    }

    /**
     * Check if user has specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->permissions->pluck('slug')->contains($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Assign role to user.
     */
    public function assignRole(Role|string $role, ?int $tenantId = null): void
    {
        $roleModel = is_string($role) ? Role::where('slug', $role)->firstOrFail() : $role;
        $tenantId = $tenantId ?? $this->active_tenant_id;

        $this->roles()->syncWithoutDetaching([
            $roleModel->id => ['tenant_id' => $tenantId]
        ]);
    }
}

