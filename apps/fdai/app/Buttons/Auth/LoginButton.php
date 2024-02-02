<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Auth;
use App\Http\Urls\IntendedUrl;
use App\Logging\ConsoleLog;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class LoginButton extends AuthButton {
	public const PATH = AuthButton::PATH . "/login";
    protected function generatePath(): string{ return self::PATH; }
	protected function getPath(): string{ return self::PATH; }
	protected function generateImage(): string{ return ImageUrls::LOGIN; }
	protected function generateFontAwesome(): string{ return FontAwesome::LOGIN; }
	protected function generateTitle(): string{ return "Login"; }
	/**
	 * @param array $params
	 * @return string
	 */
	public static function getRedirectUrl(array $params = []): string{
        try {
            IntendedUrl::setToCurrent();
        } catch (\Throwable $e) {
            ConsoleLog::exception($e);
            //le($e);
        }
		$url = LoginButton::url($params);
		$url = self::addParams($url);
		return $url;
	}
}
