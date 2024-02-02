<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\TrackingReminder;
use App\Exceptions\QMException;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\View\Request\QMRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
class SkipAllTrackingReminderNotificationsController extends PostController {
	public const ERROR_VARIABLES_ACCESS_FORBIDDEN = 'You are not authorized to access tracking reminders for user "%".';
	public function post(){
		$trackingReminderId = null;
		$body = QMRequest::body();
		$userId = QMAuth::id();
		$isAdmin = QMAuth::isAdmin();
		if($userId != QMAuth::id() && !$isAdmin){
			throw new QMException(QMException::CODE_FORBIDDEN, 'Access Forbidden', $userId);
		}
		if(isset($requestParams['trackingReminderId'])){
			$trackingReminderId = $req->get('trackingReminderId');
		}
		if(isset($body['trackingReminderId'])){
			$trackingReminderId = $body['trackingReminderId'];
		}
		if(!$trackingReminderId){
			throw new BadRequestHttpException("Please provide trackingReminderId!");
		}
		QMTrackingReminderNotification::deleteAllPastReminderNotifications($userId, $trackingReminderId);
		return $this->writeJsonWithGlobalFields(201, [
			'status' => '201',
			'success' => true,
			'message' => 'Tracking reminder notification skipped successfully',
		]);
	}
}
