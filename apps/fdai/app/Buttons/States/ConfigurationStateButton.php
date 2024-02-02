<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\AppSettings\AppSettings;
use App\Buttons\IonicButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class ConfigurationStateButton extends IonicButton {
	public $accessibilityText = 'App Builder';
	public $action = AppSettings::APP_BUILDER_URL;
	public $icon = 'ion-settings';
	public $id = 'configuration-state-button';
	public $fontAwesome = FontAwesome::TOOLS_SOLID;
	public $image = ImageUrls::ACTIVITIES_SCREWDRIVER;
	public $link = AppSettings::APP_BUILDER_URL;
	public $ionIcon = 'ion-settings';
	public $stateName = 'app.configuration';
	public $stateParams = [];
	public $text = 'App Builder';
	public $title = 'App Builder';
	public $tooltip = "The App Builder allows anyone to create their own digital health app in minutes without any coding.  Their application can then also be built and released on iOS, Android, and as a Chrome browser extension. ";
	public $menus = [];
	public function __construct(AppSettings $as = null){
		if($as){
			$this->setParam(AppSettings::FIELD_CLIENT_ID, $as->getClientId());
		}
		parent::__construct();
		$this->link = AppSettings::APP_BUILDER_URL;
	}
}
