<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\TrackingReminder;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
class GetFutureTrackingReminderNotificationController extends GetController {
	public const ERROR_VARIABLES_ACCESS_FORBIDDEN = 'You are not authorized to access tracking reminders for user "%".';
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		$requestParams = $this->params();
		$requestParams['reminderTime'] = '(gt)' . date('Y-m-d H:i:s');
		$trackingReminderNotifications =
			QMTrackingReminderNotification::getTrackingReminderNotifications(QMAuth::getQMUserIfSet(), $requestParams);
		return $this->writeJsonWithGlobalFields(200, [
			'success' => true,
			'data' => $trackingReminderNotifications,
		]);
	}
}
