<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\UserVariable;
use App\Slim\Controller\PostController;
use App\Variables\QMUserVariable;
class DeleteUserVariableController extends PostController {
	public function post(){
		$v = QMUserVariable::fromRequest();
		$v->hardDeleteWithRelations("User posted API request to delete");
		$this->getApp()->writeJsonWithGlobalFields(201, [
			'status' => 201,
			'success' => true,
		]);
	}
}
