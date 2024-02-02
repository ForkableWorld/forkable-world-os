<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection HttpUrlsUsage */
/** @noinspection PhpUnused */
namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class MetabaseButton extends AdminButton {
	public const METABASE_BASE_URL = 'https://metabase.curedao.org';
	public $title = "Metabase";
	public $tooltip = "Data analysis and visualization";
	public $link = self::METABASE_BASE_URL;
	public $image = ImageUrls::ANALYSIS;
	public $fontAwesome = FontAwesome::ANALYSIS;
	public function __construct(string $path = null, string $title = null){
		parent::__construct();
		if($title){$this->setTextAndTitle($title);}
		if($path){$this->setUrl(self::METABASE_BASE_URL . $path);}
	}
}
