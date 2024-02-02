<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\OAuth2;
use App\Logging\QMLog;
use App\Slim\Controller\Controller;
use App\Slim\Model\Auth\OAuth2Server;
use App\Slim\View\Request\QMRequest;
use OAuth2 as OAuth;
/** Class CreateAccessTokenController
 * @package App\Slim\Controller\OAuth2
 */
class CreateAccessTokenController extends Controller {
	public function initPost(){
		$_POST = QMRequest::body();
		$request = OAuth\Request::createFromGlobals();
		$server = OAuth2Server::get();
		$response = $server->handleTokenRequest($request);
		if($response->getParameter('error_description')){
			QMLog::error($response->getParameter('error_description'));
		}
		if($response instanceof OAuth\Response){
			//$response->send();
			return $this->writeJsonWithoutGlobalFields($response->getStatusCode(), 
			                                           $response->getParameters(), 
			                                           null, 
			                                           $response->getHttpHeaders());
		} else{
			QMLog::error('Could not handle token request', (array)$response);
		}
	}
}
