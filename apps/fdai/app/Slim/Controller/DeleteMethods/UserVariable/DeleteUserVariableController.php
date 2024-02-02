<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\DeleteMethods\UserVariable;
use App\Slim\Controller\DeleteController;
use App\Variables\QMUserVariable;
class DeleteUserVariableController extends DeleteController {
	public function delete(){
		$v = QMUserVariable::fromRequest();
		$v->hardDelete("User posted API request to delete");
		$this->getApp()->writeJsonWithGlobalFields(204, [
			'status' => 204,
			'success' => true,
		]);
	}
}
