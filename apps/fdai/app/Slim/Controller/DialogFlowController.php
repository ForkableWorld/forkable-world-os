<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller;
use App\DataSources\Connectors\GoogleLoginConnector;
use App\Exceptions\UnauthorizedException;
use App\Intents\QMIntent;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use Dialogflow\WebhookClient;
class DialogFlowController extends PostController {
	public $webhookClient;
	private $intent;
	/**
	 * @return void
	 * @throws UnauthorizedException
	 */
	public function post(){
		$app = $this->getApp();
		$app->setCacheControlHeader(5 * 60);
		try {
			$user = QMAuth::getQMUser();
		} catch (UnauthorizedException $e) {
			$user = GoogleLoginConnector::loginByRequest();
		}
		if(!$user){
			$this->getWebhookClient()->reply("Not authenticated");
		} else{
			$this->fulfillIntent();
		}
		$client = $this->getWebhookClient();
		$object = $client->render();
		//$app->write(201, 'application/json', json_encode($object));
		$app->writeJsonWithoutGlobalFields(201, $object);
	}
	/**
	 * @return QMIntent
	 */
	private function fulfillIntent(){
		return $this->intent = QMIntent::fulfillAndResponseToIntent($this->getWebhookClient());
	}
	/**
	 * @return WebhookClient
	 */
	public function getWebhookClient(){
		return $this->webhookClient ?: $this->setWebhookClient();
	}
	/**
	 * @return WebhookClient
	 */
	public function setWebhookClient(){
		return $this->webhookClient = QMRequest::getWebHookClient();
	}
}
