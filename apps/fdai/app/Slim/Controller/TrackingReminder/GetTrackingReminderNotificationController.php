<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\TrackingReminder;
use App\Exceptions\UnauthorizedException;
use App\Models\TrackingReminderNotification;
use App\Slim\Controller\GetController;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Utils\APIHelper;
/** Class GetTrackingReminderNotificationController
 * @package App\Slim\Controller\TrackingReminder
 */
class GetTrackingReminderNotificationController extends GetController {
	public const ERROR_VARIABLES_ACCESS_FORBIDDEN = 'You are not authorized to access tracking reminders for user "%".';
	/**
	 * @throws UnauthorizedException
	 */
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		$notifications = QMTrackingReminderNotification::getPastTrackingReminderNotifications($this->params());
		$notifications = TrackingReminderNotification::toDBModels($notifications);
		if(APIHelper::apiVersionIsBelow(4)){
			return $this->writeJsonWithGlobalFields(200, [
				'success' => true,
				'data' => $notifications,
			]);
		} else{
			return $this->writeJsonWithGlobalFields(200, ['trackingReminderNotifications' => $notifications]);
		}
	}
}
