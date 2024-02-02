<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Buttons\Admin;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class HorizonButton extends DebugButton {
	const PATH = '/horizon/dashboard';
	public $title = "Horizon Queue Manager";
	public $link = self::PATH;
	public $image = ImageUrls::AGRICULTURE_SUNNY;
	public $fontAwesome = FontAwesome::LIST_ALT;
	public $tooltip = "Due to the complexity of the analytics and the computing resource demands, just-in-time analysis has been implemented using a custom analysis prioritization and queueing mechanism. ";
	protected function getPath(): string{ return static::PATH; }
	public function requiresAdmin(): bool{
		return true;
	}
}
