<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
class UpgradeStateButton extends IonicButton {
	public $accessibilityText = 'Upgrade';
	public $action = '/#/app/upgrade';
	public $icon = 'ion-star';
	public $id = 'upgrade-state-button';
	public $ionIcon = IonIcon::star;
	public $link = '/#/app/upgrade';
	public $stateName = 'app.upgrade';
	public $stateParams = [];
	public $text = 'Upgrade';
	public $title = 'Upgrade';
	public $tooltip = 'Upgrade';
	public $menus = [];
	public $fontAwesome = FontAwesome::STAR;
	public $image = ImageUrls::ESSENTIAL_COLLECTION_STAR;
	public function __construct(){
		parent::__construct();
		$this->setTextAndTitle(app_display_name() . " Plus");
	}
}
