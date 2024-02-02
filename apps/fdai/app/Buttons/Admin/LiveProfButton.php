<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection HttpUrlsUsage */
/** @noinspection PhpUnused */
namespace App\Buttons\Admin;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\QMProfile;
use App\Utils\UrlHelper;
class LiveProfButton extends DebugButton {
	const PATH = '/profiler';
	public $title = "LiveProf";
	public $tooltip = "App performance analyzer";
	public $link = self::PATH;
	public $image = ImageUrls::BASIC_FLAT_ICONS_CLOCK;
	public $fontAwesome = FontAwesome::CLOCK;
	protected function getPath(): string{ return static::PATH; }
	public function getUrl(array $params = []): string{
		$url = QMProfile::getLastProfileUrl();
		if($url){
			return $this->link = UrlHelper::addParams($url, $params);
		}
		return parent::getUrl($params);
	}
}
