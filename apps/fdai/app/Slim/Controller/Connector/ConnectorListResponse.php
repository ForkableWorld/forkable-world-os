<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ 
/** @noinspection PhpUnused */
namespace App\Slim\Controller\Connector;
use App\DataSources\QMDataSource;
use App\Exceptions\UnauthorizedException;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMResponseBody;
use App\Slim\View\Request\QMRequest;
class ConnectorListResponse extends QMResponseBody {
	public $connectors;
	/**
	 * ConnectorsResponse constructor.
	 * @param QMDataSource $connectors
	 * @throws UnauthorizedException
	 */
	public function __construct(array $connectors){
		$this->connectors = $connectors;
		$this->addSessionTokenObjectIfNecessary();
		if(!$this->sessionTokenObject && QMRequest::getParam('clientUserId')){
			$this->addSessionTokenObjectIfNecessary();
			$user = QMAuth::getQMUser();
			le("No session token object!");
		}
		parent::__construct();
	}
}
