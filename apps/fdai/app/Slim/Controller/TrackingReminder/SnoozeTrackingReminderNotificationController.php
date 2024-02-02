<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\TrackingReminder;
use App\Slim\Controller\PostController;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\QMSlim;
class SnoozeTrackingReminderNotificationController extends PostController {
	public function post(){
		$app = QMSlim::getInstance();
		/** @var mixed[] $body */
		$body = $app->getRequestJsonBodyAsArray(false);
		QMTrackingReminderNotification::handleSubmittedNotification($body, QMTrackingReminderNotification::SNOOZE);
		return $this->writeJsonWithGlobalFields(201, [
			'status' => '201',
			'success' => true,
			'message' => 'Tracking reminder notification snoozed successfully',
		]);
	}
}
