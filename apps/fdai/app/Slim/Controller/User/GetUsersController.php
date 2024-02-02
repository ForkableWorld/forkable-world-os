<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\User;
use App\Slim\Controller\GetController;
use App\Slim\Model\User\UsersResponseBody;
class GetUsersController extends GetController {
	public function get(){
		$response = new UsersResponseBody();
		return $this->writeJsonWithGlobalFields(200, $response);
	}
}
