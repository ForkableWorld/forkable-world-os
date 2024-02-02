<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Connector;
use App\AppSettings\AppSettings;
use App\Exceptions\ClientNotFoundException;
use App\Files\FileHelper;
use App\Http\Urls\IntendedUrl;
use App\Models\Application;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Controller\GetController;
use App\Slim\QMSlim;
use App\Utils\UrlHelper;
/** Class IntegrationJsController
 * @package App\Slim\Controller\Connector
 */
class IntegrationJsController extends GetController {
	/** @var  QMSlim */
	private $app;
	/** @var  string */
	private string $baseUrl;
	public function get(){
		//QMAuthenticator::getOrAuthenticateUser();
		//Memory::$cache[QMGlobals::DO_NOT_REQUIRE_CLIENT_ID] = true;
		$this->app = QMSlim::getInstance();
		$this->baseUrl = UrlHelper::origin();
		$path = $this->app->request->getPath();
		if(stripos($path, '/integration.js') !== false){
			$this->getIntegrationJs();
		} elseif(stripos($path, '/connect.js') !== false){
			$this->getConnectJs();
		} else{
			$this->getConnectMobilePage();
		}
	}
	private function getIntegrationJs(){
		$script = file_get_contents(PROJECT_ROOT . '/public/qm-connect/integration.js');
		$script = str_replace('{{{baseUrl}}}', $this->baseUrl, $script);
		// Causes "Could not find app with client id: CLIENT_ID"
		$clientID = BaseClientIdProperty::fromRequest(false);
		if($clientID !== 'CLIENT_ID'){
			$appSettings = AppSettings::findByClientId($clientID);
			$script = str_replace('CLIENT_ID', $clientID, $script);
			$iconUrl = $appSettings->getAdditionalSettings()->getAppImages()->appIcon;
			$script = str_replace('__APP_ICON__', $iconUrl, $script);
		}
		$request = $this->app->request();
		if($request->get('showButton')){
			$script = str_replace('showButton: false', 'showButton: true', $script);
		}
		//$script = str_replace('QuantiModoIntegration', AppSettings::getAppDisplayName() . 'Integration', $script);
		IntendedUrl::setToCurrent();
		$this->app->write(200, 'application/x-javascript', $script);
	}
	public function getConnectMobilePage(){
		$this->app->response->headers->set('Content-Type', 'text/html');
		IntendedUrl::setToCurrent();
		$this->app->render('ConnectMobile.php', ['baseUrl' => $this->baseUrl]);
	}
	private function getConnectJs(){
		$script = file_get_contents(FileHelper::absPath('public/qm-connect/connect.js'));
		$script = str_replace('{{{baseUrl}}}', $this->baseUrl, $script);
		//IntendedUrl::setToCurrent();  // Why would we want to redirect to the js script?
		$this->app->write(200, 'application/x-javascript', $script);
	}
}
