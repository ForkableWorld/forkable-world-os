<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\V3;
use App\Exceptions\QMException;
use App\Slim\Controller\DeleteController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\QMSlim;
class DeleteTrackingReminderControllerV3 extends DeleteController {
	public function delete(){
		$body = $this->getRequestJsonBodyAsArray(false);
		if(isset($body['id'])){
			$trackingReminderId = $body['id'];
		}
		if(QMSlim::getInstance()->request()->get('id')){
			$trackingReminderId = QMSlim::getInstance()->request()->get('id');
		}
		if(!isset($trackingReminderId)){
			throw new QMException(400, 'Please supply the tracking reminder id.');
		}
		QMTrackingReminder::deleteTrackingReminder(QMAuth::id(), $trackingReminderId);
		return $this->writeJsonWithGlobalFields(204, [
			'status' => 204,
			'success' => true,
			'message' => 'Tracking reminder deleted successfully',
			'data' => ['trackingReminderNotifications' => QMTrackingReminderNotification::getPastQMTrackingReminderNotifications()],
		]);
	}
}
