<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\AppSettings;
use App\Exceptions\QMException;
use App\Models\Collaborator;
use App\Slim\Controller\PostController;
use App\Slim\Model\User\QMUser;
class PostUpgradeController extends PostController {
	public function post(){
		$requestBody = $this->getRequestJsonBodyAsArray(false);
		if(!$this->getClientId()){
			throw new QMException(400, 'Please provide clientId');
		}
		Collaborator::authCheck($this->getClientId());
		$r = QMUser::freeUpgrade($requestBody['userId'], $this->getClientId());
		return $this->writeJsonWithGlobalFields(201, ['upgradeResponse' => $r]);
	}
}
