<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\User;
use App\DataSources\Connectors\GoogleLoginConnector;
use App\Exceptions\ClientNotFoundException;
use App\Slim\Controller\PostController;
class GoogleIdTokenController extends PostController {
	/**
	 * @throws ClientNotFoundException
	 */
	public function post(){
		$this->getApp()->writeJsonWithGlobalFields(201, [
			'status' => 201,
			'success' => true,
			'user' => GoogleLoginConnector::loginByRequest(),
		], JSON_UNESCAPED_SLASHES);
	}
}
