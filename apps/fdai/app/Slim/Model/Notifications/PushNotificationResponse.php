<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\Logging\QMLog;
use App\Slim\Model\DBModel;
/** Class PushNotificationResponse
 * @package App\Slim\Model\Notifications
 */
abstract class PushNotificationResponse extends DBModel {
	public $success = true;
	public $error;
	public function __construct(){
		$this->updatedAt = $this->createdAt = date('Y-m-d H:i:s');
	}
	/**
	 * @param bool $success
	 */
	public function setSuccess($success){
		$this->success = (bool)$success;
	}
	/**
	 * @param string $error
	 */
	public function setError($error){
		QMLog::error("PushNotificationResponse: " . $error);
		$this->error = $error;
	}
}
