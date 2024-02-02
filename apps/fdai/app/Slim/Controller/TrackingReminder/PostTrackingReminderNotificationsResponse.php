<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMResponseBody;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Storage\Memory;
use App\Variables\QMUserVariable;
class PostTrackingReminderNotificationsResponse extends QMResponseBody {
	/**
	 * @var QMUserVariable[]
	 */
	public $userVariables = [];
	/**
	 * @var QMTrackingReminderNotification[]
	 */
	public $trackingReminderNotifications = [];
	/**
	 * @var QMMeasurement[]
	 */
	public $measurements = [];
	/**
	 * @var string
	 */
	public $message;
	/**
	 * PostTrackingReminderNotificationsResponse constructor.
	 * @param string|null $message
	 * @param QMTrackingReminderNotification|null $n
	 * @param int $responseCode
	 */
	public function __construct(string $message = null, QMTrackingReminderNotification $n = null,
		int $responseCode = 201){
		parent::__construct(null, $responseCode);
		$this->message = $message;
		if($user =
			QMAuth::getQMUser()){ // From push notifications, we post notifications without auth. In this case we shouldn't return additional data
			$measurements = Memory::getNewMeasurementsForUserByVariable($user->getId());
			foreach($measurements as $variableName => $byDate){
				$this->measurements[$variableName] = QMMeasurement::toDBModels($byDate);
			}
			$this->userVariables = QMUserVariable::fromMemoryWhereUserId($user->getId());
			$notifications = QMTrackingReminderNotification::getPastQMTrackingReminderNotifications([
				TrackingReminderNotification::FIELD_USER_ID => $user->getUserId(),
				'limit' => 20,
			]);
			QMTrackingReminderNotification::validateNotificationIds($notifications);
			$this->trackingReminderNotifications = $notifications;
		}
	}
}
