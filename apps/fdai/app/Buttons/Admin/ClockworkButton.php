<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection HttpUrlsUsage */
/** @noinspection PhpUnused */
namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
use App\Computers\ThisComputer;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\EnvOverride;
use App\Utils\UrlHelper;
class ClockworkButton extends AdminButton {
	const PATH = '/__clockwork/app';
	public $title = "Clockwork";
	public $tooltip = "App performance analyzer";
	public $link = self::PATH;
	public $image = ImageUrls::BASIC_FLAT_ICONS_CLOCK;
	public $fontAwesome = FontAwesome::CLOCK;
	public function __construct(){ 
		parent::__construct(); 
		$ip = ThisComputer::ip();
		if(EnvOverride::isLocal()){
			$ip = UrlHelper::LOCAL_QM_HOST;
		}
		$url = "http://$ip".static::PATH;
		$this->setUrl($url);
	}
	public function getUrl(array $params = []): string{
		return parent::getUrl($params);
	}
	public static function requestUrl(): string{
		$id = clock()->request()->id;
		$me = new static();
		return $me->getUrl() . "/" . $id;
	}
	protected function getPath(): string{ return static::PATH; }
	public function requiresAdmin(): bool{
		return !EnvOverride::isLocal();
	}
}
