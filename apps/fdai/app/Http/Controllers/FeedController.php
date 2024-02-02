<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers;
use App\Cards\QMCard;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\NoDeviceTokensException;
use App\Logging\QMLog;
use App\Slim\Controller\Feed\PostUserFeedController;
use App\Slim\Controller\Feed\UserFeedResponse;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\View\Request\QMRequest;

class FeedController extends PostUserFeedController {
    /**
     * @return void
     * @throws \App\Exceptions\UnauthorizedException
     */
    public function get(){
		$this->setCacheControlHeader(60);
		$this->writeJsonWithGlobalFields(200,
			new UserFeedResponse(QMAuth::getAuthenticatedUserOrThrowException()->getUser(), null));
	}

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ClientNotFoundException
     * @throws NoDeviceTokensException
     * @throws \App\Exceptions\UnauthorizedException
     */
    public function store()
    {
        $submitted = QMRequest::body();
        if(is_array($submitted) && !isset($submitted[0])){
            $submitted = (object)$submitted;
        }
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
                try {
                    QMLog::logicExceptionIfNotProductionApiRequest("Not sure how to handle this: " . json_encode($params));
                } catch (\Throwable $e) {
                    le($e);
                }
            }
        }
        $response = new UserFeedResponse(QMAuth::getUser(), $this->cardsToReturn);
        $cardsToReturn = $response->getCards();
        foreach($cardsToReturn as $card){
            $id = $card->getId();
            if(in_array($id, $submittedIds, true)){
                le("Returning $id even though the same id was in submitted cards!");
            }
        }
        return response(json_encode($response), 201);
    }
}
