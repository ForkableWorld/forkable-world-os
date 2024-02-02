<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\AppSettings;
use App\AppSettings\AppSettings;
use App\DataSources\QMClient;
use App\Exceptions\InvalidClientIdException;
use App\Exceptions\QMException;
use App\Exceptions\UnauthorizedException;
use App\Slim\Controller\DeleteController;
use App\Slim\Middleware\QMAuth;
class DeleteClientAppController extends DeleteController {
	/**
	 * @throws QMException
	 * @throws InvalidClientIdException
	 * @throws UnauthorizedException
	 */
	public function delete(){
		$clientId = $this->getClientId();
		if(!$clientId){
			throw new QMException(400, 'Please provide clientId');
		}
		$client = AppSettings::get($clientId);
		$u = QMAuth::getQMUser();
		if($u->getId() !== $client->getUserId()){
			throw new UnauthorizedException("You must be the application owner to deleted it! ");
		}
		$success = QMClient::delete($this->getClientId(), "we got API request to delete");
		return $this->writeJsonWithGlobalFields(204, ['success' => $success]);
	}
}
