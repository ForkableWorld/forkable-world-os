<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\UserTag;
use App\Exceptions\UserVariableNotFoundException;
use App\Slim\Controller\PostController;
use App\Variables\QMUserTag;
class DeleteParentUserTagController extends PostController {
	/**
	 * @throws UserVariableNotFoundException
	 */
	public function post(){
		return $this->writeJsonWithGlobalFields(204, [
			'status' => 204,
			'success' => true,
			'data' => QMUserTag::handleDeleteParentUserTagRequest(),
		]);
	}
}
