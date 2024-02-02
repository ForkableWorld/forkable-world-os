<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
class CaptureIpTrait {
	private $ipAddress = null;
	public function getClientIp(){
		if(\App\Utils\Env::get('HTTP_CLIENT_IP')){
			$ipAddress = \App\Utils\Env::get('HTTP_CLIENT_IP');
		} elseif(\App\Utils\Env::get('HTTP_X_FORWARDED_FOR')){
			$ipAddress = \App\Utils\Env::get('HTTP_X_FORWARDED_FOR');
		} elseif(\App\Utils\Env::get('HTTP_X_FORWARDED')){
			$ipAddress = \App\Utils\Env::get('HTTP_X_FORWARDED');
		} elseif(\App\Utils\Env::get('HTTP_FORWARDED_FOR')){
			$ipAddress = \App\Utils\Env::get('HTTP_FORWARDED_FOR');
		} elseif(\App\Utils\Env::get('HTTP_FORWARDED')){
			$ipAddress = \App\Utils\Env::get('HTTP_FORWARDED');
		} elseif(\App\Utils\Env::get('REMOTE_ADDR')){
			$ipAddress = \App\Utils\Env::get('REMOTE_ADDR');
		} else{
			$ipAddress = config('settings.nullIpAddress');
		}
		return $ipAddress;
	}
}
