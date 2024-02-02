<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Policies;
use App\Models\User;
use App\Models\Variable;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
class VariablePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * @param User $user
     * @return mixed
     * @noinspection PhpUnused*/
    public function viewAny(User $user){
        return true;
    }
    /**
     * Determine whether the user can view the model.
     * @param  User $user
     * @param  Variable  $model
     * @return mixed
     */
    public function view(User $user, $model): bool{
        return true; // TODO:  Handle this better
        if($user->isAdmin()){return true;}
        if($model->is_public !== false){
            return true;
        }
        $name = QMRequest::getParam(['q', 'search', 'name']);
        if(!$name){
            return false;
        }
        if($name === $model->name){
            return true;
        }
        if(in_array($name, $model->synonyms)){
            return true;
        }
        if($model->number_of_user_variables > 1){
            return true;
        }
        return false;
    }
    /**
     * Determine whether the user can create models.
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user){
        return true;
    }
    /**
     * Determine whether the user can update the model.
     * @param  User  $user
     * @param  Variable  $model
     * @return mixed
     */
    public function update(User $user, $model){
        if($user->getId() === $model->creator_user_id){
            return true;
        }
        return QMAuth::isAdmin();
    }
    /**
     * Determine whether the user can delete the model.
     * @param  User  $user
     * @param Variable  $model
     * @return mixed
     */
    public function delete(User $user, $model){
        return QMAuth::isAdmin();
    }
    /**
     * Determine whether the user can restore the model.
     * @param  User  $user
     * @param  Variable  $model
     * @return mixed
     */
    public function restore(User $user, $model){
        return true;
    }
    /**
     * Determine whether the user can permanently delete the model.
     * @param  User  $user
     * @param  Variable  $model
     * @return mixed
     */
    public function forceDelete(User $user, $model){
        return QMAuth::isAdmin();
    }
}
