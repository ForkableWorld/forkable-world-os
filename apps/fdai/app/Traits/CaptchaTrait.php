<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use ReCaptcha\ReCaptcha;
use Request;
trait CaptchaTrait {
	public function captchaCheck(){
		$response = Request::get('g-recaptcha-response');
		$remoteip = $_SERVER['REMOTE_ADDR'];
		$secret = config('settings.reCaptchSecret');
		$recaptcha = new ReCaptcha($secret);
		$resp = $recaptcha->verify($response, $remoteip);
		if($resp->isSuccess()){
			return true;
		}
		return false;
	}
}
