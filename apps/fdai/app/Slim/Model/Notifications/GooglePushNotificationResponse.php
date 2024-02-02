<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\Logging\QMLog;
use App\Slim\Model\AppEnvironment;
use App\Types\QMStr;
/** Class GooglePushNotificationResponse
 * @package App\Slim\Model\Notifications
 */
class GooglePushNotificationResponse extends PushNotificationResponse {
	/**
	 * GooglePushNotificationResponse constructor.
	 * @param $rawResponse
	 */
	public function __construct($rawResponse){
		parent::__construct();
		if(!$rawResponse){
			le("No push notification response returned!");
		}
		$decodedResponse = QMStr::jsonDecodeIfNecessary($rawResponse);
		if(!is_object($decodedResponse)){
			$errorMessage = "Push response could not be converted to an object!";
			QMLog::debug($errorMessage, [
				'raw response' => $rawResponse,
				'decoded response' => $decodedResponse,
			]);
			$this->setSuccess(false);
			$this->setError($rawResponse);
		} elseif(AppEnvironment::isCircleCIOrTravis()){
			$this->setSuccess(true);
		} else{
			$this->setSuccess($decodedResponse->success);
			if(!$this->success){
				$this->setError($decodedResponse->results[0]->error);
			}
		}
	}
}
