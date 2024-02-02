<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\TrackingReminder;
use App\Exceptions\ExceptionHandler;
use App\Slim\Controller\PostController;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use Exception;
class PostTrackingReminderNotificationsController extends PostController {
	public function post(){
		$body = $this->getBody();
		if(!$body){$body = qm_request()->query();}
		$responseCode = 201;
		$responseMessage = 'Tracking reminder notifications posted successfully!';
		if(is_object($body) && isset($body->trackingReminderNotificationId)){
			$body = [$body];
		}
		foreach($body as $submitted){
			// Sometimes body is an object like {"0":{"trackingReminderNotificationId":1,"modifiedValue":3,"action":"track"},"clientId":"oauth_test_client"}
			if(!is_object($submitted) && !is_array($submitted)){
				continue;
			}
			try {
				$n = QMTrackingReminderNotification::handleSubmittedNotification($submitted);
			} catch (Exception $exception) {
				ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($exception); // Let's continue with other notifications but return the error
				$responseCode = $exception->getCode();
				$responseMessage = $exception->getMessage();
			}
		}
		$response = new PostTrackingReminderNotificationsResponse($responseMessage, $n ?? null, $responseCode);
		$this->getApp()->writeJsonWithGlobalFields($responseCode, $response);
	}
}
