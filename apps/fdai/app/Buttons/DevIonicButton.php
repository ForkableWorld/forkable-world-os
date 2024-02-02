<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Utils\UrlHelper;
class DevIonicButton extends QMButton {
	public static function toDevIonicUrl(string $url): string{
		return str_replace(UrlHelper::IONIC_ORIGIN, UrlHelper::DEV_IONIC_ORIGIN, $url);
	}
}
