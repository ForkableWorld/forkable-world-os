<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection HttpUrlsUsage */
/** @noinspection PhpUnused */
namespace App\Buttons\Admin;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class TelescopeButton extends DebugButton {
	const PATH = '/telescope';
	public $title = "Telescope";
	public $tooltip = "App performance analyzer";
	public $link = self::PATH;
	public $image = ImageUrls::SCIENCE_TELESCOPE;
	public $fontAwesome = FontAwesome::MICROSCOPE_SOLID;
	protected function getPath(): string{ return static::PATH; }
}
