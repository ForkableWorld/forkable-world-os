<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Auth;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class RegistrationButton extends AuthButton {
	public const PATH = AuthButton::PATH . "/register";
	protected function generatePath(): string{ return self::PATH; }
	protected function getPath(): string{ return self::PATH; }
	protected function generateImage(): string{ return ImageUrls::REGISTERED; }
	protected function generateFontAwesome(): string{ return FontAwesome::REGISTERED; }
	protected function generateTitle(): string{ return "Sign Up"; }

}
