<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\User;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\QMException;
use App\Models\User;
use App\Properties\Base\BaseTimezoneProperty;
use App\Properties\User\UserEarliestReminderTimeProperty;
use App\Properties\User\UserEmailProperty;
use App\Properties\User\UserGetPreviewBuildsProperty;
use App\Properties\User\UserLatestReminderTimeProperty;
use App\Properties\User\UserPhoneNumberProperty;
use App\Properties\User\UserPhoneVerificationCodeProperty;
use App\Properties\User\UserPrimaryOutcomeVariableIdProperty;
use App\Properties\User\UserPushNotificationsEnabledProperty;
use App\Properties\User\UserSendPredictorEmailsProperty;
use App\Properties\User\UserSendReminderNotificationEmailsProperty;
use App\Properties\User\UserSmsNotificationsEnabledProperty;
use App\Properties\User\UserTrackLocationProperty;
use App\Properties\User\UserUserLoginProperty;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Types\QMArr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Str;
/** Class PostUserSettingsController
 * @package App\Slim\Controller\User
 */
class PostUserSettingsController extends PostController {
	/**
	 * @return JsonResponse|Response
	 * @throws ModelValidationException
	 */
	public function post(){
        $requestBody = (array)$this->getBody();
		if($response = $this->handleNotificationSettingsWithoutAuthCheck($requestBody)){
			return $response;
		}
		$data = [];
		if(isset($requestBody['register'])){
			$user = QMAuth::getQMUser();
			$login = UserUserLoginProperty::fromRequest(true);
			if($user && $login && $login === $user->user_login){
				throw new BadRequestException("User $user->loginName already exists");
			} else {
				User::validatePasswordCreateNewUserAndLogin($requestBody);
			}
		} else{
			$user = $this->getUser();
			if(isset($requestBody['productId'])){
				$purchaseId = $user->upgradeSubscription($requestBody);
				if($purchaseId){
					$data['purchaseId'] = $purchaseId;
				}
			} else{
				$this->handleUserSettingsRequest($requestBody, $user);
			}
		}
		$data['user'] = $this->getUser();
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 201,
			'success' => true,
			'user' => $data['user'],
			'data' => $data,
		]);
	}
	/**
	 * @param array $body
	 * @param QMUser $QMUser
	 * @throws ModelValidationException
	 */
	public function handleUserSettingsRequest(array $body, QMUser $QMUser){
		$settingChanged = $this->handleNotificationSettingsRequest($body, $QMUser);
		$user = $QMUser->getUser();
		if($tz = BaseTimezoneProperty::fromRequest()){
			$settingChanged = $QMUser->setTimeZone($tz) || $settingChanged;
		}
		if(array_key_exists('ethAddress', $body)){
			$QMUser->ethAddress = $user->eth_address = $body['ethAddress'];
			$settingChanged = true;
			$user->save();
		}
		if(array_key_exists('eth_address', $body)){
			$QMUser->ethAddress = $user->eth_address = $body['eth_address'];
			$settingChanged = true;
			$user->save();
		}
		if(array_key_exists('trackLocation', $body)){
			$settingChanged =
				UserTrackLocationProperty::setTrackLocation($QMUser, $body['trackLocation']) || $settingChanged;
		}
		if(array_key_exists('shareAllData', $body)){
			$settingChanged = $QMUser->setShareAllData($body['shareAllData']) || $settingChanged;
		}
		if(array_key_exists('subscriptionProvider', $body)){
			$settingChanged = $QMUser->setSubscriptionProvider($body) || $settingChanged;
		}
		if(array_key_exists('phoneNumber', $body)){
			$settingChanged = UserPhoneNumberProperty::setPhoneNumber($QMUser, $body['phoneNumber']) || $settingChanged;
		}
		if(array_key_exists('phoneVerificationCode', $body)){
			$settingChanged =
				UserPhoneVerificationCodeProperty::setPhoneVerificationCode($QMUser, $body['phoneVerificationCode']) ||
				$settingChanged;
		}
		$primaryOutcomeVariableId = QMArr::getValue($body, [User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID]);
		if($primaryOutcomeVariableId && $primaryOutcomeVariableId !== $QMUser->primaryOutcomeVariableId){
			$settingChanged =
				UserPrimaryOutcomeVariableIdProperty::updatePrimaryOutcomeVariableIdNameAndCreateReminder($primaryOutcomeVariableId,
				                                                                                          $QMUser);
		}
		if(!$settingChanged){
			throw new  BadRequestException('Either could not save setting or it has not changed! Please contact info@quantimo.do if you need assistance');
		}
	}
	/**
	 * @param array $body
	 * @param QMUser $user
	 * @return bool
	 */
	public function handleNotificationSettingsRequest(array $body, QMUser $user): bool{
		$settingChanged = false;
		if(array_key_exists('latestReminderTime', $body) && array_key_exists('earliestReminderTime', $body) &&
			$body['earliestReminderTime'] > $body['latestReminderTime']){
			throw new  BadRequestException('earliestReminderTime cannot exceed latestReminderTime');
		}
		if(!array_key_exists('latestReminderTime', $body) && array_key_exists('earliestReminderTime', $body) &&
			$body['earliestReminderTime'] > $user->latestReminderTime){
			throw new  BadRequestException('earliestReminderTime cannot exceed latestReminderTime');
		}
		if(array_key_exists('latestReminderTime', $body) && !array_key_exists('earliestReminderTime', $body) &&
			$body['latestReminderTime'] < $user->earliestReminderTime){
			throw new BadRequestException('earliestReminderTime cannot exceed latestReminderTime');
		}
		if(array_key_exists('latestReminderTime', $body)){
			$settingChanged =
				UserLatestReminderTimeProperty::setLatestReminderTime($user, $body['latestReminderTime']) ||
				$settingChanged;
		}
		if(array_key_exists('earliestReminderTime', $body)){
			$settingChanged =
				UserEarliestReminderTimeProperty::setEarliestReminderTime($user, $body['earliestReminderTime']) ||
				$settingChanged;
		}
		if(array_key_exists('pushNotificationsEnabled', $body)){
			$settingChanged = UserPushNotificationsEnabledProperty::setPushNotificationsEnabled($user,
					$body['pushNotificationsEnabled']) || $settingChanged;
		}
		if(array_key_exists('smsNotificationsEnabled', $body)){
			$settingChanged = UserSmsNotificationsEnabledProperty::setSmsNotificationsEnabled($user,
					$body['smsNotificationsEnabled']) || $settingChanged;
		}
		if(array_key_exists('combineNotifications', $body)){
			$settingChanged = $user->setCombineNotifications($body['combineNotifications']) || $settingChanged;
		}
		if(array_key_exists('sendReminderNotificationEmails', $body)){
			$settingChanged = UserSendReminderNotificationEmailsProperty::setSendReminderNotificationEmails($user,
					$body['sendReminderNotificationEmails']) || $settingChanged;
		}
		if(array_key_exists('sendPredictorEmails', $body)){
			$settingChanged =
				UserSendPredictorEmailsProperty::setSendPredictorEmails($user, $body['sendPredictorEmails']) ||
				$settingChanged;
		}
		if(array_key_exists('getPreviewBuilds', $body)){
			$settingChanged =
				UserGetPreviewBuildsProperty::setGetPreviewBuilds($user, $body['getPreviewBuilds']) || $settingChanged;
		}
		return $settingChanged;
	}
	/**
	 * @return null|QMUser
	 */
	private function getUser(): ?QMUser{
		if($u = QMAuth::getQMUser()){
			$u->getOrSetAccessTokenString();
			return $u;
		}
		throw new QMException(QMException::CODE_FORBIDDEN, 'User not authenticated');
	}
	/**
	 * @param array $requestBody
	 * @return JsonResponse|null
	 * @throws ModelValidationException
	 */
	private function handleNotificationSettingsWithoutAuthCheck(array $requestBody){
		$email = UserEmailProperty::pluck($requestBody);
		if(!$email){return null;} // Normal settings change
		try {
			$QMUser = QMAuth::getQMUser();
			if($QMUser){return null;} // Normal settings change
		} catch (\Throwable $e) {}
		$user = User::whereUserEmail($email)->first();
		if(!$user){
			return null; // Might be changing email address
		} // Normal settings change
		$QMUser = $user->getQMUser();
		$changed = [];
		foreach($requestBody as $key => $value){
			if(QMUser::isNotificationSetting($key)){
				$value = QMArr::pluckValue($requestBody, $key);
				if($value === null){
					continue;
				}
				$snake = Str::snake($key);
				$user->setAttribute($snake, $value);
				$changed[$key] = $value;
			}
		}
		if($changed){
			$user->save();
			return $this->writeJsonWithGlobalFields(201, [
				'message' => 'Changed notification settings',
				'user' => $QMUser->getNotificationSettings(),
				'changes' => $changed
			]);
		}
		return null;
	}
	private function getPublicUser(){
		if($email = $this->getBodyOrQueryParam('userEmail')){
			$user = QMUser::findByEmail($email);
			if($user){
				$user->unsetPasswordAndTokenProperties();
				return $user;
			}
		}
		return null;
	}
}
