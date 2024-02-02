<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\Logging\QMLog;
use App\Properties\Base\BasePlatformProperty;
use App\Utils\Env;
use RuntimeException;

/** Class GooglePushNotification
 * @package App\Slim\Model\Notifications
 */
class GooglePushNotification extends PushNotification {
	//public const GCM_URL = 'https://gcm-http.googleapis.com/gcm/send';
	public const GCM_URL = 'https://fcm.googleapis.com/fcm/send';
	//public const DEBUG_TOKEN_STRING = "cgnj6vKEyPc:APA91bFX-68B1DRZSjn_Ax0mf0_oKY9_ZQwOPYvLptKRxOT43Wo7uI5QPXm0wwQ7KhcPTDL7X0SySkFk_8nJGPPM19e8WVcWDKho9WCx98ksBIrPaW3aMDu49xUbmtEjEdZ04fEgX72N";
	public const DEBUG_TOKEN_STRING = false;
	private $postFields;
	/**
	 * GooglePushNotification constructor.
	 * @param $row
	 * @param $pushData
	 */
	public function __construct($row, $pushData){
		parent::__construct($row, $pushData);
		$this->sendPush();
	}
	public function sendPush(){
		$postFields =
			$this->getPushDataArray(); // Can't call getPostFields twice because it messes up force-start setter
		$ch = curl_init(); // Open connection
        // Check if initialization had gone wrong*
        if ($ch === false) {
            throw new RuntimeException('failed to initialize');
        }
        // Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, self::GCM_URL);
		curl_setopt($ch, CURLOPT_POST, true);
		$headers = $this->getHeaders();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Disabling SSL Certificate support temporarily
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		$rawResponse = curl_exec($ch);
        // Check the return value of curl_exec(), too
        if ($rawResponse === false) {
            throw new RuntimeException(curl_error($ch), curl_errno($ch));
        }
        // Check HTTP return code, too; might be something else than 200
        $httpReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//QMLog::info("Sending " . $this->getUser()->loginName . " Google push: " . $this->getPostFields());
		$message = substr($postFields, 0, 140) . '...';

		if(Env::APP_DEBUG()){
			$message = $postFields;
		}
		if($this->getUser()->isMike() && $this->getDeviceTokenObject()->getPlatform() ===
			BasePlatformProperty::PLATFORM_WEB){
			$message = "Sending web GCM push to mike for client " . $this->getDeviceTokenObject()->getClientId();
			//$this->logError($message);
			$this->logInfo($message);
		} else{
			$this->logInfo($message);
		}
		$this->setResponse($rawResponse);
		if(stripos($rawResponse, "Sender is not authorized") !== false){
			QMLog::error("Please add your IP to https://console.developers.google.com/apis/credentials/key/6?project=quantimo-do&pli=1",
				[], $rawResponse);
			le("Add your IP to https://console.developers.google.com/apis/credentials/key/6?project=quantimo-do&pli=1");
		}
	}
	/**
	 * @param $rawResponse
	 */
	private function setResponse($rawResponse){
		$this->response = new GooglePushNotificationResponse($rawResponse);
		$this->setSuccess($this->getResponse()->success);
		$this->setErrorMessage($this->getResponse()->error);
	}
	/**
	 * @return string
	 */
	private function getPushDataArray(): string
    {
		$pushDataArray = $this->getPushData()->compressAndPrepareForDelivery();
		$pushDataArray['foreground'] =
			false; // event handler will be called without the app being brought to the foreground
		$pushDataArray['content-available'] = 1; //makes plugin calls all of your notification event handlers
		// i.e. updateLocationVariablesAndPostMeasurementIfChanged & refreshTrackingReminderNotifications
		// See https://github.com/phonegap/phonegap-plugin-push/blob/2660b51da66e791ff342d027ea6afa4313281e28/docs/PAYLOAD.md#background-notifications
		// https://github.com/phonegap/phonegap-plugin-push/blob/2660b51da66e791ff342d027ea6afa4313281e28/docs/PAYLOAD.md#application-force-closed
		$pushDataArray['force-start'] =
			$this->getForceStart(); // If you add force-start: 1 to the data payload the application will be restarted in background even if it was force closed.
		if(!$this->getDeviceTokenObject()->requireAcknowledgement()){
			$pushDataArray['acknowledge'] = true;
		}
		$postFields['data'] = $pushDataArray;
		$postFields['to'] = $this->getDeviceTokenObject()->deviceToken;
		if(self::DEBUG_TOKEN_STRING){
			$postFields['to'] = self::DEBUG_TOKEN_STRING;
		}
		return $this->postFields = json_encode($postFields);
	}
	/**
	 * @return array
	 */
	private function getHeaders(){
		$googleCloudMessagingKey = self::getGoogleCloudMessagingKey();
		$headers = [
			'Authorization: key=' . $googleCloudMessagingKey,
			'Content-Type: application/json',
		];
		return $headers;
	}
	/**
	 * @return array|false|string
	 */
	public static function getGoogleCloudMessagingKey(){
		$googleCloudMessagingKey = \App\Utils\Env::get('GOOGLE_CLOUD_MESSAGING_API_KEY');
		$googleCloudMessagingKey = str_replace('"', '', $googleCloudMessagingKey);
		if(str_contains($googleCloudMessagingKey, '"')){
			le("GOOGLE_CLOUD_MESSAGING_API_KEY contains quote marks!");
		}
		if(empty($googleCloudMessagingKey)){
			le("Please set GOOGLE_CLOUD_MESSAGING_API_KEY");
		}
		return $googleCloudMessagingKey;
	}
	/**
	 * Can only use force start on first notification on Android or it brings the app to the foreground
	 * @return int
	 */
	private function getForceStart(){
		if($this->getPushData()->getForceStart() !== null){
			return $this->getPushData()->getForceStart();
		}
		return $this->getDeviceTokenObject()->getForceStartForPushNotification();
	}
}
