<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\TrackingReminder;
use App\Exceptions\ModelValidationException;
use App\Exceptions\TrackingReminderNotificationNotFoundException;
use App\Slim\Controller\PostController;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\QMSlim;
class TrackTrackingReminderNotificationController extends PostController {
	/**
	 * @throws ModelValidationException
	 * @throws TrackingReminderNotificationNotFoundException
	 */
	public function post(){
		$app = QMSlim::getInstance();
		$body = $app->getRequestJsonBodyAsArray(false);
		QMTrackingReminderNotification::handleSubmittedNotification($body, QMTrackingReminderNotification::TRACK);
		return $this->writeJsonWithGlobalFields(201, [
			'status' => '201',
			'success' => true,
			'message' => 'Tracking reminder notification tracked successfully',
		]);
	}
}
