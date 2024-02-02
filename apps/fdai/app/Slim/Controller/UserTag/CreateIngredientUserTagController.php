<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\UserTag;
use App\Exceptions\UserVariableNotFoundException;
use App\Slim\Controller\PostController;
use App\Slim\QMSlim;
use App\Variables\QMUserTag;
class CreateIngredientUserTagController extends PostController {
	/**
	 * @throws UserVariableNotFoundException
	 */
	public function post(){
		$app = QMSlim::getInstance();
		$body = $app->getRequestJsonBodyAsArray(false);
		$response = QMUserTag::addIngredientUserTag($body);
		return $this->writeJsonWithGlobalFields(201, $response);
	}
}
