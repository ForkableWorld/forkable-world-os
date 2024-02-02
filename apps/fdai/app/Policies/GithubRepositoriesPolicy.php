<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Policies;

use App\Models\GithubRepositories;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GithubRepositoriesPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any item.
     *
     * @param  User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the item.
     *
     * @param User $user
     * @param GithubRepositories $item
     * @return mixed
     */
    public function view(User $user, GithubRepositories $item)
    {
        return true;
    }

    /**
     * Determine whether the user can create item.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the item.
     *
     * @param User $user
     * @param GithubRepositories $item
     * @return mixed
     */
    public function update(User $user, GithubRepositories $item)
    {
        return $user->id === $item->user_id;
    }

    /**
     * Determine whether the user can delete the item.
     *
     * @param User $user
     * @param GithubRepositories $item
     * @return mixed
     */
    public function delete(User $user, GithubRepositories $item)
    {
        return $user->id === $item->user_id;
    }

    /**
     * Determine whether the user can restore the item.
     *
     * @param User $user
     * @param GithubRepositories $item
     * @return mixed
     */
    public function restore(User $user, GithubRepositories $item)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the item.
     *
     * @param User $user
     * @param GithubRepositories $item
     * @return mixed
     */
    public function forceDelete(User $user, GithubRepositories $item)
    {
        //
    }
}
