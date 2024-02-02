<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\Logging\QMLog;
class IonicPushNotification extends PushNotification {
	/**
	 * IonicPushNotification constructor.
	 * @param $row
	 * @param $pushData
	 */
	public function __construct($row, $pushData){
		parent::__construct($row, $pushData);
		$message = $this->getPushData()->message;
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => 'https://api.ionic.io/push/notifications',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "{\"tokens\": [\"$row->deviceToken\"], \"profile\": \"production\", \"notification\": {\"message\": \"$message\"}}",
			CURLOPT_HTTPHEADER => [
				'authorization: Bearer ' . getenv('IONIC_PUSH_AUTH_TOKEN'),
				'cache-control: no-cache',
				'content-type: application/json',
				'postman-token: '. getenv('POSTMAN_TOKEN')
			],
		]);
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if($err){
			QMLog::error('cURL Error #:' . $err, ['$deviceTokenObject' => $row]);
		} else{
			QMLog::error($response, ['$deviceTokenObject' => $row]);
		}
	}
}
