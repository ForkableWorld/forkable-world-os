<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;
class RolePolicy extends BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the specified user can create a role.
     *
     * @param User|null $user
     * @return bool
     */
    public function create(?User $user)
    {
        return $user && $user->isAdmin();
    }

    /**
     * Determine if the specified user can update the given role.
     *
     * @param User|null $user
     * @param Role $role
     * @return bool
     */
    public function update(?User $user, $role)
    {
        return $user && $user->isAdmin();
    }

    /**
     * Determine if the specified user can assign the specified role.
     *
     * @param User|null $user
     * @param Role $role
     * @return bool
     */
    public function assign(?User $user, Role $role)
    {
        return $user && $user->isAdmin();
    }
    /**
     * Determine if the specified user can assign permissions to the given role.
     *
     * @param User|null $user
     * @param Role $role
     * @return bool
     */
    public function assignPermission(?User $user, Role $role)
    {
        return $user && $user->isAdmin();
    }

    /**
     * Determine if the specified user can deny permissions to the given role.
     *
     * @param User|null $user
     * @param Role $role
     * @return bool
     */
    public function denyPermission(?User $user, Role $role)
    {
        return $user && $user->isAdmin();
    }
}
