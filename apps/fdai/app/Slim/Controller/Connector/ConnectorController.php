<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Connector;
use App\Buttons\Auth\LoginButton;
use App\DataSources\QMConnector;
use App\Exceptions\BadRequestException;
use App\Exceptions\ExceptionHandler;
use App\Http\Middleware\QMStartSession;
use App\Http\Urls\FinishUrl;
use App\Http\Urls\IntendedUrl;
use App\Slim\Controller\Controller;
use App\Slim\Middleware\QMAuth;
use App\UI\Alerter;
use App\Utils\UrlHelper;
use Illuminate\Http\RedirectResponse;
use Slim\Exception\Stop;
/** Class ConnectorController
 * @package App\Slim\Controller\Connector
 */
class ConnectorController extends Controller {
	/**
	 * @param string $connectorName Connector name
	 * @param string $method Method name ('update', 'connect', 'disconnect', 'info')
	 * @throws \Throwable
	 */
	public function map(string $connectorName, string $method){
		try {
			$finalCallback = IntendedUrl::get();
			$user = QMAuth::getQMUser();
			$qmRequest = qm_request();
			$session1 = session();
			if(!$user){
				(new QMStartSession($session1))->startSession(request(), $session1);
				$user = \Auth::user();
				$user = \Auth::getUser();
			}
			$params = $qmRequest->input();
			if(!$user && $method === 'connect'){
				$connector = QMConnector::getConnectorByNameOrId($connectorName);
				if(!$connector->providesUserProfileForLogin){
					$session = $session1;
					return LoginButton::redirectToLoginOrRegister();
				}
				$r = $connector->connect($params);
			} else{
				$user = QMAuth::getQMUser('', true);
				$connector = QMConnector::getConnectorByNameOrId($connectorName, $user->getId());
				switch($method) {
					case 'connect':
						try {
							$r = $connector->connect($params);
						} catch (ConnectException $e){
							if(\request()->acceptsHtml()){
								$html = $connector->getEditHTML($_GET);
								$this->outputHtmlToBrowser($html);
								return $html;
							}
						}
						break;
					case 'update':
						try {
							$r = new ConnectorUpdateResponse($connector->getConnectionIfExists());
						} catch (\Throwable $e) {
							$r = $connector->requestImport();
						}
						break;
					case 'disconnect':
						$connection = $connector->getConnectionIfExists();
						$connection->disconnect(QMConnector::USER_DISCONNECT_REQUEST);
						$r = new ConnectorResponse($connector, $connector->name . '/disconnect');
						break;
					case 'info':
						$connection = $connector->getConnectionIfExists();
						$r = new ConnectorUpdateResponse($connection);
						break;
					default:
						throw new BadRequestException('Incorrect connection method');
				}
			}
			if(!isset($r)){
				throw new BadRequestException('Incorrect connection method');
			}
			if($r instanceof ConnectException && $this->clientAcceptsHtml()){
				return $this->outputHtmlToBrowser($connector->getEditHTML());
			} elseif($r instanceof RedirectResponse){
				return UrlHelper::redirect($r->getTargetUrl(), $r->getStatusCode());
			} elseif($this->noRedirect()){
				return $this->writeJsonWithGlobalFields($r->code, $r);
			} elseif(isset($r->location)){
				return UrlHelper::redirect($r->location, $r->code);
			} else{
				return FinishUrl::sendToFinishPage();
			}
		} catch (\Throwable $e) {
			if($e instanceof Stop){
				throw $e;
			}
			ExceptionHandler::dumpOrNotify($e);
			//if(!AppMode::isProduction()){throw $e;}
			if(\request()->acceptsHtml()){
				if($e instanceof BadRequestException){
					Alerter::errorWithHelpButtonToast($e->getMessage());
				} else{
					Alerter::errorWithHelpButtonToast("Connection Issue");
				}
				return FinishUrl::sendToFinishPage();
			} else{
				throw $e;
			}
		}
	}
}
