<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Feed;
use App\AppSettings\AppSettings;
use App\Cards\QMCard;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\NoDeviceTokensException;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Models\Application;
use App\Properties\User\UserIdProperty;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\MessagesMessage;
use App\Slim\Model\Phrases\QuestionPhrase;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Types\ObjectHelper;
class PostUserFeedController extends PostController {
	protected $cardsToReturn = [];
	/**
	 * @return \Illuminate\Http\Response
	 * @throws \App\Exceptions\ClientNotFoundException
	 * @throws \App\Exceptions\UnauthorizedException
	 */
	public function post(){
		$submitted = $this->getBody();
		if(!is_array($submitted)){
			$submitted = [$submitted];
		}
		$submittedIds = [];
		foreach($submitted as $params){
			if(isset($params->parameters)){
				$params = $params->parameters;
			}
			if(isset($params->intent) && stripos($params->intent, "question") !== false){
				$this->handleQuestion($params);
				continue;
			}
			if(isset($params->trackingReminderNotificationId)){
				$submittedIds[] = $params->trackingReminderNotificationId;
				try {
					$n = QMTrackingReminderNotification::handleSubmittedNotification($params);
				} catch (\Throwable $e) {
					ExceptionHandler::dumpOrNotify($e);
				}
			} elseif(isset($params->studyId)){
				continue;
			} elseif(isset($params->message)){
				$this->sendPushNotifications($params->message);
			} else{
				QMLog::logicExceptionIfNotProductionApiRequest("Not sure how to handle this: " . json_encode($params));
			}
		}
		$user = QMAuth::getUser();
		if(!$user){
			throw new UnauthorizedException();
		}
		$response = new UserFeedResponse($user, $this->cardsToReturn);
		$cardsToReturn = $response->getCards();
		foreach($cardsToReturn as $card){
			$id = $card->getId();
			if(in_array($id, $submittedIds, true)){
				le("Returning $id even though the same id was in submitted cards!");
			}
		}
		return $this->writeJsonWithGlobalFields(201, $response);
	}
	/**
	 * @param $cardParameterObject
	 * @throws ClientNotFoundException
	 */
	protected function handleQuestion($cardParameterObject){
		$text = ObjectHelper::getValueOfFirstMatchingProperty($cardParameterObject, [
			'text',
			'phrase',
		]);
		$this->logError("Unknown input: " . $text);
		$p = new QuestionPhrase($text, [UserIdProperty::USER_ID_MIKE]);
		try {
			$p->saveAndSend();
		} catch (NoDeviceTokensException $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
		}
		$card = new QMCard();
		$app = Application::getByClientId();
		$card->setAvatar($app->getAppIcon());
		$card->setHeaderTitle($app->appDisplayName);
		$card->setSubHeader("Just now");
		$card->setContent("I'm going to ask my creator about $text and get back to you.   ");
		$this->cardsToReturn[] = $card;
	}
	/**
	 * @param $message
	 */
	protected function sendPushNotifications($message){
		$bpMessage = new MessagesMessage();
		$bpMessage->populateFieldsByArrayOrObject($message);
		$bpMessage->sendPushNotifications();
	}
}
