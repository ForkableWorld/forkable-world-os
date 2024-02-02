<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use ApnsPHP_Push;
class ApplePushNotificationResponse extends PushNotificationResponse {
	/**
	 * ApplePushNotificationResponse constructor.
	 * @param ApnsPHP_Push $applePush
	 */
	public function __construct($applePush){
		parent::__construct();
		$aErrorQueue = $applePush->getErrors();  // Examine the error message container
		if(!empty($aErrorQueue)){
			$this->setSuccess(false);
			$this->setError($aErrorQueue[1]['ERRORS'][0]['statusMessage']);
		}
	}
}
