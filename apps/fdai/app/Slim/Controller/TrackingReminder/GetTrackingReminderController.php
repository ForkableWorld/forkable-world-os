<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\TrackingReminder;
use App\Properties\Base\BaseScopeProperty;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\TrackingRemindersResponse;
use App\Slim\View\Request\QMRequest;
use App\Types\QMArr;
use App\Utils\APIHelper;
class GetTrackingReminderController extends GetController {
	public const ERROR_VARIABLES_ACCESS_FORBIDDEN = 'You are not authorized to access tracking reminders for user "%".';
	public function get(){
		$this->setCacheControlHeader(10);  // TODO: Figure out how to increase this using lastModified
		$app = $this->getApp();
		$requestParams = QMRequest::getInput();
		$user = QMAuth::getQMUser(BaseScopeProperty::READ_SCOPE, true);
		$trackingReminders = QMTrackingReminder::getTrackingReminders($user, $requestParams);
		usort($trackingReminders, function($a, $b){
			return strcmp(strtolower($a->variableName), strtolower($b->variableName));
		});
		// Why is unsetNullProperties necessary?
		$trackingReminders = QMArr::unsetNullPropertiesOfObjectsInArray($trackingReminders, ['stopTrackingDate']);
		/* Commented due to slowing down reminders response
		$numberOfPendingNotifications = TrackingReminderNotification::getPendingTrackingReminderNotificationCount($userId);
		foreach ($trackingReminders as $trackingReminder){ $trackingReminder->numberOfPendingNotifications = $numberOfPendingNotifications; } */
		/** @var QMTrackingReminder $trackingReminder */
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, new TrackingRemindersResponse($trackingReminders));
		} elseif(APIHelper::isApiVersion(3)){
			return $this->writeJsonWithoutGlobalFields(200, $trackingReminders);
		} else{
			return $this->writeJsonWithGlobalFields(200, [
				'success' => true,
				'data' => $trackingReminders,
			]);
		}
	}
}
