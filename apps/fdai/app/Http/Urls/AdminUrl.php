<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Urls;
use App\Slim\View\Request\QMRequest;
use App\Utils\UrlHelper;
abstract class AdminUrl extends AbstractUrl {
	public static function getAdminUrl(string $path, array $params = []): string{
		return UrlHelper::getUrl('/admin/' . $path, $params);
	}
	public static function adminUrl(string $path, array $params = []): string{
		if($path){
			$path = "/admin/$path";
		} else{
			$path = "/admin";
		}
		return QMRequest::getAppHostUrl($path, $params);
	}
}
