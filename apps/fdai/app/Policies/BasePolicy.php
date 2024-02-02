<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Policies;
use App\Files\FileHelper;
use App\Models\BaseModel;
use App\Models\User;
use App\Types\QMStr;
use Illuminate\Auth\Access\HandlesAuthorization;
class BasePolicy {
	public const POLICY_create = 'create';
	public const POLICY_delete = 'delete';
	public const POLICY_forceDelete = 'forceDelete';
	public const POLICY_restore = 'restore';
	public const POLICY_update = 'update';
	public const POLICY_view = 'view';
	public const POLICY_viewAny = 'viewAny';
	use HandlesAuthorization;
	public static function generatePolicies(){
		$classes = BaseModel::getClassNames();
		foreach($classes as $class){
			$short = QMStr::toShortClassName($class);
			$content = FileHelper::getContents('app/Policies/PolicyStub.php');
			$content = str_replace('BaseModel', $short, $content);
			$content = str_replace('PolicyStub', $short . "Policy", $content);
			FileHelper::writeByFilePath("app/Policies/$short" . "Policy.php", $content);
		}
	}
	/**
	 * Determine whether the user can create models.
	 * @param User $user
	 * @return bool
	 */
	public function create(User $user){
		return true;
	}
	/**
	 * Determine whether the user can delete the model.
	 * @param User $user
	 * @param BaseModel $model
	 * @return bool
	 */
	public function delete(User $user, $model){
		$res = $model->canWriteMe($user);
		return $res;
	}
	/**
	 * Determine whether the user can permanently delete the model.
	 * @param User $user
	 * @param BaseModel $model
	 * @return bool
	 */
	public function forceDelete(User $user, $model){
		return $user->isAdmin();
	}
	/**
	 * Determine whether the user can restore the model.
	 * @param User $user
	 * @param BaseModel $model
	 * @return bool
	 */
	public function restore(User $user, $model){
		return $model->canWriteMe($user);
	}
	/**
	 * Determine whether the user can update the model.
	 * @param User $user
	 * @param BaseModel $model
	 * @return bool
	 */
	public function update(User $user, $model){
        if($model->readerIsOwnerOrAdmin($user)){
            return true;
        }
        if(method_exists($this, 'patientGrantedAccess')){
            return $model->patientGrantedAccess('write', $user);
        } else{
            return false;
        }
	}
	/**
	 * Determine whether the user can view the model.
	 * @param User $user
	 * @param BaseModel $model
	 * @return bool
	 */
	public function view(User $user, $model){
		return $model->canReadMe($user);
	}
	/**
	 * Determine whether the user can view any models.
	 * @param User $user
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function viewAny(User $user){
		return $user->isAdmin();
	}
}
