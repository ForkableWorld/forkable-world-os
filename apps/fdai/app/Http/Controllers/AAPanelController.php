<?php

namespace App\Http\Controllers;
use App\Computers\JenkinsSlave;
use App\Utils\UrlHelper;
class AAPanelController extends Controller
{
    public function __invoke(string $provided)
    {
		$ip = JenkinsSlave::getRunnerIP($provided);
	    $url = AAPanelController::getAAPanelUrl($ip);
	    return UrlHelper::redirect($url);
    }
	/**
	 * @param string $provided
	 * @return string
	 */
	public static function getAAPanelUrl(string $ip): string{
		return JenkinsSlave::getAAPanelUrl($ip);
	}
}
