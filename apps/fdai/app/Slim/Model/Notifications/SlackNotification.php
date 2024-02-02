<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\AppSettings\AppStatus\BuildStatus;
use App\DataSources\QMClient;
use App\Logging\QMLog;
use App\Models\OAClient;
use App\Slim\Model\User\QMUser;
use App\Types\QMStr;
use App\Utils\APIHelper;
use App\Utils\UrlHelper;
use Maknz\Slack\Client;
use Maknz\Slack\Message;
use OAuth\OAuth2\Token\StdOAuth2Token;
use stdClass;
class SlackNotification {
	public const SLACK_TOKEN_META_KEY = 'slack_token';
	private $data;
	/**
	 * @var string
	 */
	private $text;
	/**
	 * @var string
	 */
	private $channel;
	/**
	 * @var string
	 */
	private $icon;
	/**
	 * @var array
	 */
	private $attachments;
	private $slackTokensForCollaborators;
	private $slackToken;
	private $url;
	/**
	 * SlackNotification constructor.
	 * @param string $text
	 * @param string $channel
	 * @param string $icon
	 * @param array $attachments
	 */
	public function __construct($text = "hi", $channel = "engineering", $icon = ":longbox:", $attachments = []){
		$this->url = getenv('SLACK_WEBHOOK_URL');
		$this->text = $text;
		$this->channel = $channel;
		$this->icon = $icon;
		$this->attachments = $attachments;
		$this->slackTokensForCollaborators = [];
	}
	/**
	 * @return mixed|stdClass
	 */
	public function sendSlackMessage(){
		$this->data = [
			"channel" => "#$this->channel",
			"text" => $this->text,
			"icon_emoji" => $this->icon,
		];
		return APIHelper::makePostRequest($this->url, $this->data, $this->slackToken);
	}
	/**
	 * @param $clientId
	 * @param $appType
	 * @param $downloadLink
	 * @return mixed|stdClass
	 */
	private function sendSingleBuildNotification($clientId, $appType, $downloadLink){
		$message = "Your <" . UrlHelper::getBuilderUrl($clientId) . "|" . QMStr::camelToTitle($clientId) . "> " .
			strtolower(QMStr::camelToTitle($appType)) . " build is <" . $downloadLink . "|ready>.  ";
		if($appType === 'chromeExtension'){
			$message .= QMStr::getChromeExtensionInstructionsForSlack($downloadLink);
		}
		$message .= QMStr::getAppEditInstructionsHtml($clientId);
		$this->setText($message);
		$this->setChannel('builds');
		return $this->sendSlackMessage();
	}
	/**
	 * @param string $clientId
	 * @param $buildStatus
	 * @param $betaDownloadLinks
	 * @return array[]|string
	 */
	public function sendBuildNotifications(string $clientId, $buildStatus, $betaDownloadLinks){
		$response = [];
		$this->getSlackTokensForCollaborators($clientId);
		if(!count($this->slackTokensForCollaborators)){
			return "No slack tokens for client";
		}
		foreach($this->slackTokensForCollaborators as $this->slackToken){
			foreach($buildStatus as $key => $value){
				if($value !== BuildStatus::STATUS_BUILDING && !empty($betaDownloadLinks->$key)){
					$downloadButtons[] = [
						'text' => QMStr::camelToTitle($key),
						'link' => $betaDownloadLinks->$key,
					];
					$response[$key] = $this->sendSingleBuildNotification($clientId, $key, $betaDownloadLinks->$key);
				}
			}
		}
		return $response;
	}
	/**
	 * @param $userId
	 * @return string
	 */
	private function getSlackTokensForUser($userId){
		/** @var StdOAuth2Token $slackTokenObject */
		$user = QMUser::find($userId);
		$slackTokenObject = $user->getUserMetaValue(self::SLACK_TOKEN_META_KEY);
		if($slackTokenObject){
			if(is_string($slackTokenObject)){
				QMLog::error("slackTokenObject should not be a string but is: $slackTokenObject");
				return $slackTokenObject;
			}
			return $slackTokenObject->getAccessToken();
		}
		return false;
	}
	/**
	 * @param $clientId
	 */
	private function getSlackTokensForCollaborators($clientId){
		$client = OAClient::findInMemoryOrDB($clientId);
		$client->getCollaborators();
		$collaborators = QMClient::getAllCollaborators($clientId);
		foreach($collaborators as $collaborator){
			$slackToken = $this->getSlackTokensForUser($collaborator->user_id);
			if($slackToken){
				$this->slackTokensForCollaborators[] = $slackToken;
			}
		}
	}
	/**
	 * @return string
	 */
	public function getText(): string{
		return $this->text;
	}
	/**
	 * @param string $text
	 */
	public function setText($text){
		$this->text = $text;
	}
	/**
	 * @return string
	 */
	public function getChannel(): string{
		return $this->channel;
	}
	/**
	 * @param string $channel
	 */
	public function setChannel($channel){
		$this->channel = $channel;
	}
	/**
	 * @return string
	 */
	public function getIcon(): string{
		return $this->icon;
	}
	/**
	 * @param string $icon
	 */
	public function setIcon($icon){
		$this->icon = $icon;
	}
	/**
	 * @return array
	 */
	public function getAttachments(): array{
		return $this->attachments;
	}
	/**
	 * @param array $attachments
	 */
	public function setAttachments(array $attachments){
		$this->attachments = $attachments;
	}
	/**
	 * @param string $webhook
	 * @return Client|Message
	 */
	public static function slackClient(string $webhook = null){
		if(!$webhook){
			$webhook = getenv('SLACK_WEBHOOK_URL');
		}
		$client = new Client($webhook);
		return $client;
	}
}
