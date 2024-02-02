<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\TrackingReminder;
use App\Exceptions\BadRequestException;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\DeviceToken;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Properties\Base\BaseValenceProperty;
use App\Properties\Base\BaseVariableCategoryIdProperty;
use App\Properties\UserVariable\UserVariableDefaultUnitIdProperty;
use App\Slim\Configuration\RouteConfiguration;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\View\Request\QMRequest;
use App\Types\QMArr;
use App\Types\QMStr;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
class PostTrackingReminderController extends PostController {
	public const ERROR_VARIABLE_NOT_FOUND = 'User variable id %s not found';
	public const ERROR_VARIABLE_PARAMETER_MISSING = 'Please provide an id, variableId, or a variableName.';
	public const ERROR_REMINDER_TIMES = 'Daily reminder times are no longer supported. Please use 86400 reminderFrequency and reminderStartTime for daily reminders.';
	/**
	 * @param array $body
	 * @return array
	 * @throws BadRequestException
	 */
	private static function bodyToArr(array $body): array{
		$clientId = $body['clientId'] ?? null;
		unset($body['clientId']);
		$reminders = [];
		if(isset($body['variableId']) || isset($body['variableName']) || isset($body['id'])){
			$reminders[0] = $body;
		} elseif(isset($body[0])){
			foreach($body as $i => $iValue){
				if(!is_int($i)){
					continue;
				}
				if($clientId){
					$iValue[TrackingReminder::FIELD_CLIENT_ID] = $clientId;
				}
				$reminders[$i] = QMStr::properlyFormatRequestParams($iValue);
			}
		}
		if(isset($reminders[0]['firstDailyReminderTime'], $reminders[0]['secondDailyReminderTime'])){
			$reminders = QMTrackingReminder::breakDailyTimesIntoSeparateReminders($reminders);
		}
		return $reminders;
	}
	/**
	 * @return Response
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws UnauthorizedException
	 */
	public function post(){
		$body = QMRequest::body();
		if(!$body){$body = qm_request()->query();}
		$arr = self::bodyToArr($body);
		$userVariables = [];
		$valence = null;
		foreach($arr as $item){
			$valence = $valence ?? $item['valence'] ?? null;
			$item = $this->setUserIdIfNecessary($item);
			try {
				$l = TrackingReminder::upsertOne($item, true);
			} catch (QueryException $e) {
				if(stripos($e->getMessage(), "Duplicate entry") !== false){
					QMLog::exceptionIfNotProduction($e->getMessage(), ['provided' => $item]);
					continue;
				} else{
					throw $e;
				}
			}
			$reminder = $l->getDBModel();
			$this->validateUnitAndCategory($item, $reminder);
			$uv = $reminder->getUserVariable()->getDBModel();
			$userVariables[$uv->name] = $uv;
		}
		if(!isset($reminder)){le("Could not create reminder!");}
		$response['data']['userVariables'] = array_values($userVariables);
		$response['status'] = 201;
		$response['success'] = true;
		$response = self::addTrackingRemindersToResponse($response);
		$response = self::addNotificationsToResponse($reminder, $response, $valence);
		return $this->writeJsonWithGlobalFields(201, $response);
	}
	/**
	 * @param string $valence
	 * @param QMTrackingReminderNotification $n
	 */
	private static function validateValenceOnNotifications(string $valence, $n): void{
		if($n->valence !== $valence && $n->isRating()){
			$n->exceptionIfNotProductionAPI("Wrong Valence! Submitted valence was: $valence\n" .
				"but valence on notification is: $n->valence");
		}
		if($n->isRating() && $valence === BaseValenceProperty::VALENCE_NEUTRAL){
			$buttons = $n->getButtons();
			$found = false;
			foreach($buttons as $button){
				if(stripos($button->getImage(), 'numeric') !== false){
					$found = true;
				}
			}
			if(!$found){
				$n->exceptionIfNotProductionAPI("Wrong Valence! $valence notification should have numeric rating buttons");
			}
		}
	}
	/**
	 * @param QMTrackingReminder $reminder
	 * @param string $valence
	 * @param array $notifications
	 */
	private static function validateNotificationsResponse(QMTrackingReminder $reminder, ?string $valence,
		array $notifications): void{
		if(!$notifications && $reminder->isActive()){
			$reminder->logError('Created a reminder but did not get any notifications from DB!');
		}
		QMTrackingReminderNotification::validateNotificationIds($notifications);
		$byName = QMArr::indexBy($notifications, 'variableName');
		$forReminder = $byName[$reminder->getVariableName()] ?? null;
		if(!$forReminder && $reminder->isActive() && count($notifications) < 20){
			$reminder->exceptionIfNotProductionAPI("Notification not found for reminder we just created with name "
                .$reminder->getVariableName(),
				[
					'notifications' => TrackingReminderNotification::generateDataLabIndexUrl(),
					'reminders' => TrackingReminder::generateDataLabIndexUrl(),
					'Device Tokens' => DeviceToken::generateDataLabIndexUrl(),
				]);
		}
		if($valence && $forReminder){
			self::validateValenceOnNotifications($valence, $forReminder);
		}
	}

	/**
	 * @param QMTrackingReminder $reminder
	 * @param array $response
	 * @param string|null $valence
	 * @return array
	 */
	private static function addNotificationsToResponse(QMTrackingReminder $reminder, array $response,
		string $valence = null): array{
		$qb = QMAuth::getUser()->tracking_reminder_notifications()
			->orderBy(TrackingReminderNotification::FIELD_NOTIFY_AT, BaseModel::ORDER_DIRECTION_DESC)
			->where(TrackingReminderNotification::FIELD_NOTIFY_AT, "<", db_date(time() + 1))
			->limit(TrackingReminderNotification::DEFAULT_LIMIT);
		$notifications = TrackingReminderNotification::toDBModels($qb->get());
		self::validateNotificationsResponse($reminder, $valence, $notifications);
		$response['data']['trackingReminderNotifications'] = $notifications;
		return $response;
	}
	/**
	 * @param $response
	 * @return mixed
	 */
	private static function addTrackingRemindersToResponse($response){
		$reminders = TrackingReminder::whereUserId(QMAuth::id())
			->with([
				'user_variable',
				'variable'
			])
			->get();
		$response['data']['trackingReminders'] = TrackingReminder::toDBModels($reminders);
		if(!count($response['data']['trackingReminders'])){
			QMLog::error('Created a reminder but did not get any reminders from DB!', ['response' => $response]);
		}
		return $response;
	}
	/**
	 * @param $reminder
	 * @param int|null $unitIdFromReq
	 * @param $item
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function validateNewUnitId($reminder, int $unitIdFromReq, $item){
		$newReminderUnitId = $reminder->getUnitIdAttribute();
		if($unitIdFromReq !== $newReminderUnitId){
			$l = TrackingReminder::upsertOne($item, true);
			$reminder->getUnitIdAttribute();
			QMLog::exceptionIfNotProduction("newReminderUnitId is $newReminderUnitId but unitIdFromReq is $unitIdFromReq for $reminder");
		}
	}
	/**
	 * @param $reminder
	 * @param $categoryIdFromReq
	 */
	public function validateNewCategoryId($reminder, $categoryIdFromReq): void{
		$categoryIdFromReminder = $reminder->getVariableCategoryId();
		if($categoryIdFromReq !== $categoryIdFromReminder){
			$reminder->getVariableCategoryId();
			QMLog::exceptionIfNotProduction("$categoryIdFromReq is categoryIdFromReq but categoryIdFromReminder is $categoryIdFromReminder for $reminder");
		}
	}
	/**
	 * @param $item
	 * @return mixed
	 */
	public function setUserIdIfNecessary($item){
		if(!isset($item['userId'])){
			$item['userId'] = QMAuth::getQMUser(RouteConfiguration::SCOPE_WRITE_MEASUREMENTS)->getId();
		}
		return $item;
	}
	/**
	 * @param $item
	 * @param $reminder
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function validateUnitAndCategory($item, $reminder): void{
		if($unitIdFromReq = UserVariableDefaultUnitIdProperty::pluckOrDefault($item)){
			$this->validateNewUnitId($reminder, $unitIdFromReq, $item);
		}
		if($categoryIdFromReq = BaseVariableCategoryIdProperty::pluck($item)){
			$this->validateNewCategoryId($reminder, $categoryIdFromReq);
		}
	}
}
