<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\DeleteMethods\TrackingReminder;
use App\Slim\Controller\DeleteController;
use App\Slim\Model\Reminders\QMTrackingReminder;
class DeleteTrackingReminderController extends DeleteController {
	public function delete(){
		return $this->writeJsonWithGlobalFields(204, [
			'status' => 204,
			'success' => true,
			'message' => 'Tracking reminder deleted successfully',
			'data' => ['trackingReminderNotifications' => QMTrackingReminder::handleDeleteRequest()],
		]);
	}
}
