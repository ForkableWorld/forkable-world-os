<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection HttpUrlsUsage */
/** @noinspection PhpUnused */
namespace App\Buttons\Admin;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class AstralButton extends DebugButton {
	const PATH = '/astral';
	public $title = "Astral Admin Console";
	public $tooltip = "CRUD for all your favorite models";
	public $link = self::PATH;
	public $image = ImageUrls::PHPLARAVELFRAMEWORK;
	public $fontAwesome = FontAwesome::LARAVEL;
	protected function getPath(): string{ return static::PATH; }
}
