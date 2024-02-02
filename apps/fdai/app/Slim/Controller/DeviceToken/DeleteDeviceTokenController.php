<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\DeviceToken;
use App\Exceptions\QMException;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Slim\QMSlim;
class DeleteDeviceTokenController extends PostController {
	public const ERROR_DEVICE_TOKEN_MISSING = 'Please include deviceToken in body of request';
	public const ERROR_PLATFORM_MISSING = 'Please include platform ios or android in body of request';
	public const ERROR_DEVICE_TOKEN_NOT_FOUND = 'deviceToken not found!  Stop wasting my time!';
	public function post(){
		$app = QMSlim::getInstance();
		$user = QMAuth::getQMUserIfSet();
		$requestBody = $app->getRequestJsonBodyAsArray(false);
		$platform = null;
		if(!isset($requestBody['deviceToken']) && !isset($requestBody[0]['deviceToken'])){
			throw new QMException(QMException::CODE_BAD_REQUEST, self::ERROR_DEVICE_TOKEN_MISSING);
		}
		if(isset($requestBody['deviceToken'])){
			$submittedDeviceToken = $requestBody['deviceToken'];
		} else{
			$submittedDeviceToken = $requestBody[0]['deviceToken'];
		}
		$success = QMDeviceToken::writable()->where('user_id', $user->id)->where('device_token', $submittedDeviceToken)
			->delete();
		if(!$success){
			throw new QMException(QMException::CODE_BAD_REQUEST, self::ERROR_DEVICE_TOKEN_NOT_FOUND);
		}
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 201,
			'success' => true,
		]);
	}
}
