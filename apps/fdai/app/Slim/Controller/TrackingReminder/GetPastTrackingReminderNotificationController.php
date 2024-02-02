<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\TrackingReminder;
use App\Slim\Controller\GetController;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Utils\APIHelper;
class GetPastTrackingReminderNotificationController extends GetController {
	public const ERROR_VARIABLES_ACCESS_FORBIDDEN = 'You are not authorized to access tracking reminders for user "%".';
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		$requestParams = $this->params();
		$trackingReminderNotifications =
			QMTrackingReminderNotification::getPastQMTrackingReminderNotifications($requestParams);
		if(APIHelper::apiVersionIsAbove(3)){
			return $this->writeJsonWithGlobalFields(200, ['trackingReminderNotifications' => $trackingReminderNotifications]);
		} elseif(APIHelper::isApiVersion(3)){
			$this->writeJsonWithoutGlobalFields(200, $trackingReminderNotifications);
			//$this->writeJson(200, ['trackingReminderNotifications' => $trackingReminderNotifications]);
		} else{
			return $this->writeJsonWithGlobalFields(200, ['data' => $trackingReminderNotifications]);
		}
	}
}
