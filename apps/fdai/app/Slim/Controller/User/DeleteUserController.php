<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\User;
use App\Slim\Controller\DeleteController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
/** Class DeleteUserController
 * @package App\Slim\Controller\User
 */
class DeleteUserController extends DeleteController {
	public const ENDPOINT = '/api/v3/user/delete';
	public function delete(){
		$user = QMAuth::getQMUserIfSet();
		$this->makeSureClientCreatedUser();
		$reason = QMRequest::getParam('reason', null, true);
		$success = $user->delete($reason);
		if($success){
			return $this->writeJsonWithGlobalFields(204, [
				'status' => 204,
				'success' => true,
			]);
		} else{
			return $this->writeJsonWithGlobalFields(404, [
				'status' => 404,
				'success' => false,
				'message' => "Could not delete user",
			]);
		}
	}
}
