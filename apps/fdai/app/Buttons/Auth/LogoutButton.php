<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Auth;
use App\Http\Urls\AfterLogoutUrl;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class LogoutButton extends AuthButton {
	public const PATH = AuthButton::PATH . "/logout";
	protected function generatePath(): string{ return self::PATH; }
	protected function getPath(): string{ return self::PATH; }
	protected function generateImage(): string{ return ImageUrls::ESSENTIAL_COLLECTION_CLOSE; }
	protected function generateFontAwesome(): string{ return FontAwesome::SIGN_OUT_ALT_SOLID; }
	protected function generateTitle(): string{ return "Logout"; }
	public static function getRedirectUrl(array $params = []): string{
		return AfterLogoutUrl::url();
		//return parent::getRedirectUrl($params); 
	}
}
