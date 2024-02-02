<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\DeleteMethods\UserTag;
use App\Slim\Controller\DeleteController;
use App\Variables\QMUserTag;
class DeleteIngredientUserTagController extends DeleteController {
	public function delete(){
		return $this->writeJsonWithGlobalFields(204, [
			'status' => 204,
			'success' => true,
			'data' => QMUserTag::handleDeleteIngredientUserTagRequest(),
		]);
	}
}
