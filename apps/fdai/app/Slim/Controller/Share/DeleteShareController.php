<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Share;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
class DeleteShareController extends PostController {
	/**
	 * @throws \App\Exceptions\UnauthorizedException
	 */
	public function post(){
		$clientIdToRevoke = QMRequest::fromInput('clientIdToRevoke');
		$clients = QMAuth::getQMUser()->revokeClientAccess($clientIdToRevoke);
		$r = new ShareResponse();
		$r->setAuthorizedClients($clients);
		$r->setCode(204);
		return $this->getApp()->writeJsonWithGlobalFields(204, $r);
	}
}
