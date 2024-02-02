<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\TrackingReminder;
use App\Logging\QMLog;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Notifications\IndividualPushNotificationData;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
class ReceivedTrackingReminderNotificationsController extends PostController {
	public function post(){
		$app = QMSlim::getInstance();
		/** @var IndividualPushNotificationData $pushNotificationData */
		$pushNotificationData = QMRequest::bodyAsObj();
		if(!$pushNotificationData || !isset($pushNotificationData->deviceToken)){
			$success = QMDeviceToken::writable()->where(QMDeviceToken::FIELD_USER_ID, QMAuth::id())
				->update([QMDeviceToken::FIELD_RECEIVED_AT => date('Y-m-d H:i:s')]);
			//QMLog::error("No deviceToken provided", ['submitted request body' => $pushNotificationData]);
			//throw new BadRequestHttpException("Please provide deviceToken");
		} else{
			$success =
				QMDeviceToken::writable()->where(QMDeviceToken::FIELD_DEVICE_TOKEN, $pushNotificationData->deviceToken)
					->update([QMDeviceToken::FIELD_RECEIVED_AT => date('Y-m-d H:i:s')]);
		}
		if(!$success){
			$row =
				QMDeviceToken::readonly()->where(QMDeviceToken::FIELD_DEVICE_TOKEN, $pushNotificationData->deviceToken)
					->first();
			if(!$row){
				QMLog::error("DeviceToken not found!", ['submitted request body' => $pushNotificationData]);
			} elseif($row->received_at){
				QMLog::warning("Already marked DeviceToken as received", [
					'submitted' => $pushNotificationData,
					'token row' => $row,
				]);
			} else{
				QMLog::error("Could not mark DeviceToken received", [
					'submitted' => $pushNotificationData,
					'token row' => $row,
				]);
			}
		}
		if(isset($pushNotificationData->additionalData) &&
			isset($pushNotificationData->additionalData->trackingReminderNotificationId)){
			$id = $pushNotificationData->additionalData->trackingReminderNotificationId;
			$success = QMTrackingReminderNotification::writable()->where(QMTrackingReminderNotification::FIELD_ID, $id)
				->update([QMTrackingReminderNotification::FIELD_RECEIVED_AT => date('Y-m-d H:i:s')]);
			if(!$success){
				$notificationRow =
					QMTrackingReminderNotification::readonly()->where(QMTrackingReminderNotification::FIELD_ID, $id)
						->first();
				if(!$notificationRow){
					QMLog::error("Notification not found so could not mark as received!",
						['submitted request body' => $pushNotificationData]);
				} elseif($notificationRow->received_at){
					QMLog::error("Already marked TrackingReminderNotification as received", [
						'submitted' => $pushNotificationData,
						'row' => $notificationRow,
					]);
				} else{
					QMLog::error("Could not mark TrackingReminderNotification received. Here's the row: ", [
						'trackingReminderNotificationId' => $id,
						'notification row' => $notificationRow,
					]);
				}
			}
		}
		return $this->writeJsonWithGlobalFields(201, [
			'status' => '201',
			'success' => $success,
			'message' => 'Tracking reminder notification received_at posted',
		]);
	}
}
