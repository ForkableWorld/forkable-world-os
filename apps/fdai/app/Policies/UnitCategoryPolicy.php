<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Policies;
use App\Models\UnitCategory;
use App\Models\User;
class UnitCategoryPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * @param User $user
     * @return bool
     * @noinspection PhpUnused*/
    public function viewAny(User $user): bool{
        return true;
    }
    /**
     * Determine whether the user can view the model.
     * @param User $user
     * @param  UnitCategory  $model
     * @return bool
     */
    public function view(User $user, $model): bool{
        return true;
    }
    /**
     * Determine whether the user can create models.
     * @param  User $user
     * @return mixed
     */
    public function create(User $user){
        return parent::create($user);
    }
    /**
     * Determine whether the user can update the model.
     * @param  User  $user
     * @param  UnitCategory  $model
     * @return mixed
     */
    public function update(User $user, $model){
        return parent::update($user, $model);
    }
    /**
     * Determine whether the user can delete the model.
     * @param  User  $user
     * @param  UnitCategory  $model
     * @return mixed
     */
    public function delete(User $user, $model){
        return parent::delete($user, $model);
    }
    /**
     * Determine whether the user can restore the model.
     * @param  User  $user
     * @param UnitCategory  $model
     * @return mixed
     */
    public function restore(User $user, $model){
        return parent::restore($user, $model);
    }
    /**
     * Determine whether the user can permanently delete the model.
     * @param  User  $user
     * @param  UnitCategory  $model
     * @return mixed
     */
    public function forceDelete(User $user, $model){
        return parent::forceDelete($user, $model);
    }
}
