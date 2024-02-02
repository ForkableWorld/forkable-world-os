<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Urls;
use App\Utils\IonicHelper;
use Illuminate\Support\Facades\Request;
class AfterLogoutUrl {
	/**
	 * @return string
	 */
	public static function url(): string{
		$afterLogoutGoToUrl = Request::get('afterLogoutGoToUrl');
		if(!$afterLogoutGoToUrl){
			$afterLogoutGoToUrl = Request::get('redirectTo');
		}
		if(!$afterLogoutGoToUrl){
			$afterLogoutGoToUrl = IonicHelper::getIntroUrl(['logout' => true]);
		}
		return $afterLogoutGoToUrl;
	}
}
