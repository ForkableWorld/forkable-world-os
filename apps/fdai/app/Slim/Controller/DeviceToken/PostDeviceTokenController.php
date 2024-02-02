<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\DeviceToken;
use App\DataSources\QMClient;
use App\Exceptions\BadRequestException;
use App\Exceptions\DuplicateDataException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Slim\QMSlim;
use App\Utils\AppMode;
use Illuminate\Http\Response;
class PostDeviceTokenController extends PostController {
	public const ERROR_DEVICE_TOKEN_MISSING = 'Please include deviceToken in body of request';
	public const ERROR_DEVICE_TOKEN_ALREADY_EXISTS = 'deviceToken already exists!  Stop wasting my time!';
	/**
	 * @return Response
	 * @throws ModelValidationException
	 * @throws UnauthorizedException
	 */
	public function post(){
		$submittedDeviceTokenString = $this->getDeviceTokenFromRequestBody();
		$user = QMAuth::getQMUser();
		if($user->id === UserIdProperty::USER_ID_MIKE || $user->id === UserIdProperty::USER_ID_DEMO ||
			$user->id === 78771){
			QMDeviceToken::saveTestTokenStringToFirebase($submittedDeviceTokenString, $this->getPlatformFromRequest());
		}
		if($user->isCloudTestLab()){
			$user->logError("Not saving token for cloudtestlabaccounts");
			return $this->writeJsonWithGlobalFields(201, [
				'status' => 201,
				'success' => true,
			]);
		}
		$this->makeSureDeviceTokenIsNotAlreadyInDatabase($submittedDeviceTokenString);
		$this->saveDeviceTokenToDatabase($submittedDeviceTokenString);
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 201,
			'success' => true,
		]);
	}
	/**
	 * @param string $submittedDeviceToken
	 * @throws UnauthorizedException
	 */
	private function makeSureDeviceTokenIsNotAlreadyInDatabase($submittedDeviceToken){
		$user = QMAuth::getQMUser();
		$existingToken = QMDeviceToken::readonly()->where('device_token', $submittedDeviceToken)
			->where(QMDeviceToken::FIELD_USER_ID, $user->getId())
			->where('client_id', BaseClientIdProperty::fromMemory())->whereNull(QMDeviceToken::FIELD_ERROR_MESSAGE)
			->first();  // Double check
		if($existingToken){
			throw new DuplicateDataException(self::ERROR_DEVICE_TOKEN_ALREADY_EXISTS);
		}
	}
	/**
	 * @param string $submittedDeviceTokenString
	 * @return void
	 * @throws UnauthorizedException
	 * @throws ModelValidationException
	 */
	private function saveDeviceTokenToDatabase(string $submittedDeviceTokenString): void{
		QMDeviceToken::writable()->where(QMDeviceToken::FIELD_ID, $submittedDeviceTokenString)->delete();
		$arr = [
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
			QMDeviceToken::FIELD_USER_ID => QMAuth::id(),
			QMDeviceToken::FIELD_DEVICE_TOKEN => $submittedDeviceTokenString,
			QMDeviceToken::FIELD_CLIENT_ID => $this->getDeviceTokenClientId(),
			QMDeviceToken::FIELD_PLATFORM => $this->getPlatformFromRequest(),
		];
		$token = new QMDeviceToken($arr);
		$success = $token->save();
		if($success){
			//DeviceToken::writable()->where(DeviceToken::FIELD_USER_ID, $user->id)->whereNotNull(DeviceToken::FIELD_ERROR_MESSAGE)->delete();
			QMLog::info("Inserted device token");
		} else{
			QMLog::error("Could not insert device token");
		}
	}
	/**
	 * @return string
	 * @throws BadRequestException
	 */
	private function getDeviceTokenFromRequestBody(): string{
		$requestBody = $this->getRequestJsonBodyAsArray(false);
		if(AppMode::isProduction() && str_contains(BaseClientIdProperty::fromMemory(), "Web")){
			QMLog::error('client id for device token should not be Web!');
		}
		if(str_contains(BaseClientIdProperty::fromMemory(), "Unknown")){
			QMLog::error('client id for device token should not be Unknown!');
		}
		if(!isset($requestBody['deviceToken']) && !isset($requestBody[0]['deviceToken'])){
			throw new BadRequestException(self::ERROR_DEVICE_TOKEN_MISSING);
		}
		$submittedDeviceToken = $requestBody['deviceToken'] ?? $requestBody[0]['deviceToken'];
		if(strlen($submittedDeviceToken) < 10){
			throw new BadRequestException("Device token should have at least 10 characters but is: " .
				$submittedDeviceToken);
		}
		if(stripos($submittedDeviceToken, 'blacklist') !== false){
			QMLog::error('Push device token blacklisted!');
		}
		return $submittedDeviceToken;
	}
	/**
	 * @return string
	 * @throws BadRequestException
	 */
	private function getPlatformFromRequest(): string{
		$platform = null;
		$requestBody = $this->getRequestJsonBodyAsArray(false);
		if(isset($requestBody['platform'])){
			$platform = $requestBody['platform'];
		}
		if(isset($requestBody[0]['platform'])){
			$platform = $requestBody[0]['platform'];
		}
		$platform = strtolower($platform);
		if(stripos($platform, 'chrome') !== false){
			$platform = 'chrome';
		}
		if(!$platform || !QMDeviceToken::inAllowedPlatforms($platform)){
			throw new BadRequestException("Please provide platform: " .
				implode(",", QMDeviceToken::getAllowedPlatforms()));
		}
		return $platform;
	}
	/**
	 * @return string
	 */
	private function getDeviceTokenClientId(): string{
		$clientId = $this->getClientId();
		$clientId = QMClient::replaceClientIdIfInvalid($clientId);
		return $clientId;
	}
}
