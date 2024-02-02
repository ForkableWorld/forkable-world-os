<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Actions;
use App\Slim\Model\User\QMUser;
/** @method mixed run(string $title, string $description)
 */
class DeleteUserAction extends AdminAction {
	/**
	 * Get the validation rules that apply to the action.
	 * @return array
	 */
	public function rules(){
		return [];
	}
	/**
	 * Execute the action and return a result.
	 * @return mixed
	 */
	public function handle(){
		$params = $this->validated();
		$user = QMUser::find($params['id']);
		return $user->delete($params['reason']);
	}
}
