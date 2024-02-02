<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Actions;
use App\Slim\Middleware\QMAuth;
use App\Utils\AppMode;
use App\Actions\Action;
abstract class AdminAction extends Action {
	/**
	 * Determine if the user is authorized to make this action.
	 * @return bool
	 */
	public function authorize(){
		return !AppMode::isApiRequest() || QMAuth::isAdmin();
	}
	/**
	 * Execute the action and return a result.
	 * @return mixed
	 */
	abstract public function handle();
}
